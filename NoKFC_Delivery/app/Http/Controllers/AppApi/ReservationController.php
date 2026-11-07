<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /**
     * Get list of active restaurant branches.
     */
    public function restaurants(): JsonResponse
    {
        $restaurants = Restaurant::query()
            ->where('is_active', true)
            ->withCount('tables')
            ->get();

        return response()->json($restaurants);
    }

    /**
     * Get tables with availability status for a selected restaurant and time slot.
     */
    public function availableTables(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'guest_count' => 'required|integer|min:1|max:6',
        ]);

        $restaurantId = (int) $request->input('restaurant_id');
        $date = $request->input('reservation_date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $guestCount = (int) $request->input('guest_count');

        $tables = RestaurantTable::query()
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        $result = $tables->map(function (RestaurantTable $table) use ($date, $startTime, $endTime, $guestCount) {
            $isOccupied = $table->isOccupied($date, $startTime, $endTime);
            $tooSmall = $table->seats < $guestCount;

            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'seats' => $table->seats,
                'is_occupied' => $isOccupied,
                'too_small' => $tooSmall,
                'is_available' => !$isOccupied && !$tooSmall,
            ];
        });

        return response()->json($result);
    }

    /**
     * Get user's reservations.
     */
    public function index(Request $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->where('user_id', $request->user()->id)
            ->with(['table.restaurant'])
            ->orderByDesc('reservation_date')
            ->orderByDesc('start_time')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Create a new reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_table_id' => 'required|exists:restaurant_tables,id',
            'reservation_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'guest_count' => 'required|integer|min:1|max:6',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        $reservation = DB::transaction(function () use ($validated, $user) {
            $table = RestaurantTable::query()
                ->lockForUpdate()
                ->findOrFail($validated['restaurant_table_id']);

            if (!$table->is_active) {
                throw ValidationException::withMessages([
                    'restaurant_table_id' => 'Выбранный стол временно недоступен для бронирования.',
                ]);
            }

            if ($validated['guest_count'] > $table->seats) {
                throw ValidationException::withMessages([
                    'guest_count' => "Стол №{$table->table_number} вмещает максимум {$table->seats} гостей.",
                ]);
            }

            // Check if table is occupied at this time
            if ($table->isOccupied($validated['reservation_date'], $validated['start_time'], $validated['end_time'])) {
                throw ValidationException::withMessages([
                    'restaurant_table_id' => "Стол №{$table->table_number} уже занят на выбранный промежуток времени ({$validated['start_time']} - {$validated['end_time']}). Пожалуйста, выберите другой стол или время.",
                ]);
            }

            return Reservation::query()->create([
                'user_id' => $user->id,
                'restaurant_table_id' => $table->id,
                'reservation_date' => $validated['reservation_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'guest_count' => $validated['guest_count'],
                'contact_name' => $validated['contact_name'],
                'contact_phone' => $validated['contact_phone'],
                'note' => $validated['note'] ?? null,
                'status' => Reservation::STATUS_CONFIRMED,
            ]);
        });

        $reservation->load('table.restaurant');

        return response()->json($reservation, 201);
    }

    /**
     * Cancel reservation.
     */
    public function cancel(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Доступ запрещен.'], 403);
        }

        if ($reservation->status === Reservation::STATUS_CANCELLED) {
            return response()->json(['message' => 'Бронирование уже отменено.'], 400);
        }

        $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Бронирование успешно отменено.',
            'reservation' => $reservation->fresh('table.restaurant'),
        ]);
    }
}
