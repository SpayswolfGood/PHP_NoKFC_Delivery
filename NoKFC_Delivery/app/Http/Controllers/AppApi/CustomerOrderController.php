<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerOrderController extends Controller
{

    private function getTimeBasedExtraMinutes(): int
    {
        $currentHour = (int) Carbon::now()->format('H');
        
        if ($currentHour >= 12 && $currentHour < 15) {
            return 15;
        }
        
        if ($currentHour >= 8 && $currentHour < 12) {
            return 10;
        }

        return 5;
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();

        $orders = Order::query()
            ->where('customer_id', $user->customer_id)
            ->with('items.dish')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function dishes(): JsonResponse
    {
        return response()->json(
            Dish::query()->availableForOrder()->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $totalAmount = 0;
            $maxPrepTime = 0; 
            $orderItemsData = [];

            foreach ($request->input('items') as $item) {
                $dish = Dish::query()->with('ingredients')->findOrFail($item['dish_id']);
                $orderedQty = $item['quantity'];

                foreach ($dish->ingredients as $ingredient) {
                    $requiredAmount = $ingredient->pivot->amount * $orderedQty;

                    if ($ingredient->quantity < $requiredAmount) {
                        return response()->json(['message' => "Недостаточно ингредиентов для блюда '{$dish->name}' ({$ingredient->name})."], 422, [], JSON_UNESCAPED_UNICODE);
                    }
                }

                foreach ($dish->ingredients as $ingredient) {
                    $requiredAmount = $ingredient->pivot->amount * $orderedQty;
                    $ingredient->decrement('quantity', $requiredAmount);
                }

                $totalAmount += $dish->price * $orderedQty;
                if ($dish->preparation_time > $maxPrepTime) {
                    $maxPrepTime = $dish->preparation_time;
                }

                $orderItemsData[] = [
                    'dish_id' => $dish->id,
                    'quantity' => $orderedQty,
                    'unit_price' => $dish->price,
                ];
            }

            $extraMinutes = $this->getTimeBasedExtraMinutes();
            $estimatedMinutes = $maxPrepTime + $extraMinutes;

            $existingNote = trim((string) $request->input('note', ''));
            $etaNote = "{$estimatedMinutes} min";

            $order = Order::create([
                'customer_id' => $user->customer_id,
                'delivery_address' => $request->input('delivery_address'),
                'total_amount' => $totalAmount,
                'status' => 'new',
                'delivery_time' => now()->addMinutes($estimatedMinutes),
                'note' => $existingNote !== '' ? "{$existingNote} | {$etaNote}" : $etaNote,
            ]);

            $order->items()->createMany($orderItemsData);

            return response()->json($order->load('items.dish'), 201, [], JSON_UNESCAPED_UNICODE);
        });
    }

    public function cancel(Order $order): JsonResponse
    {
        $user = auth()->user();

        if ($order->customer_id !== $user->customer_id) {
            return response()->json(['message' => 'Доступ запрещен.'], 403, [], JSON_UNESCAPED_UNICODE);
        }

        if ($order->status !== 'new') {
            return response()->json(['message' => 'Нельзя отменить заказ, который уже готовится или доставлен.'], 422, [], JSON_UNESCAPED_UNICODE);
        }

        return DB::transaction(function () use ($order) {
            $order->load('items.dish.ingredients');

            foreach ($order->items as $item) {
                if ($item->dish) {
                    foreach ($item->dish->ingredients as $ingredient) {
                        $returnedAmount = $ingredient->pivot->amount * $item->quantity;
                        $ingredient->increment('quantity', $returnedAmount);
                    }
                }
            }

            $order->update(['status' => 'cancelled']);

            return response()->json(['message' => 'Заказ успешно отменен.', 'order' => $order], 200, [], JSON_UNESCAPED_UNICODE);
        });
    }
}