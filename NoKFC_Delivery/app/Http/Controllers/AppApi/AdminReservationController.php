<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReservationController extends Controller
{
    /**
     * List all reservations with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::query()
            ->with(['table.restaurant', 'user'])
            ->latest('reservation_date')
            ->latest('start_time');

        if ($request->filled('restaurant_id')) {
            $restaurantId = $request->input('restaurant_id');
            $query->whereHas('table', fn($q) => $q->where('restaurant_id', $restaurantId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->where('reservation_date', $request->input('date'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', $search)
                  ->orWhere('contact_phone', 'like', $search)
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $search)->orWhere('email', 'like', $search));
            });
        }

        return response()->json($query->get());
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Reservation::STATUS_CONFIRMED,
                Reservation::STATUS_COMPLETED,
                Reservation::STATUS_CANCELLED,
            ])],
        ]);

        $reservation->update(['status' => $validated['status']]);

        return response()->json($reservation->load(['table.restaurant', 'user']));
    }

    /**
     * Delete reservation.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->delete();

        return response()->json(['message' => 'Бронирование успешно удалено.'], 200);
    }

    /**
     * Get tables by restaurant for admin table management.
     */
    public function tables(Request $request): JsonResponse
    {
        $query = RestaurantTable::query()->with('restaurant')->orderBy('table_number');

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->input('restaurant_id'));
        }

        return response()->json($query->get());
    }

    /**
     * Create a new table in a restaurant (capacity from 2 to 6 seats).
     */
    public function storeTable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => [
                'required',
                'string',
                Rule::unique('restaurant_tables')->where(fn($q) => $q->where('restaurant_id', $request->input('restaurant_id'))),
            ],
            'seats' => ['required', 'integer', 'min:' . RestaurantTable::MIN_SEATS, 'max:' . RestaurantTable::MAX_SEATS],
        ], [
            'seats.min' => 'У стола должно быть от ' . RestaurantTable::MIN_SEATS . ' до ' . RestaurantTable::MAX_SEATS . ' мест.',
            'seats.max' => 'У стола должно быть от ' . RestaurantTable::MIN_SEATS . ' до ' . RestaurantTable::MAX_SEATS . ' мест.',
            'table_number.unique' => 'Стол с таким номером уже существует в этом ресторане.',
        ]);

        $table = RestaurantTable::query()->create([
            'restaurant_id' => $validated['restaurant_id'],
            'table_number' => $validated['table_number'],
            'seats' => $validated['seats'],
            'is_active' => true,
        ]);

        return response()->json($table->load('restaurant'), 201);
    }

    /**
     * Delete a table.
     */
    public function destroyTable(RestaurantTable $table): JsonResponse
    {
        // Cancel future active reservations on this table
        $table->reservations()
            ->where('reservation_date', '>=', now()->toDateString())
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->update(['status' => Reservation::STATUS_CANCELLED]);

        $table->delete();

        return response()->json(['message' => 'Стол успешно удален.'], 200);
    }
}
