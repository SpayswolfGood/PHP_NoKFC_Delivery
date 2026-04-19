<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function dishes(): JsonResponse
    {
        return response()->json(
            Dish::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(
            Order::query()
                ->with(['items.dish', 'courier:id,name'])
                ->where('customer_id', $user->customer_id)
                ->latest()
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'delivery_address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => ['required', 'integer', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated, $user) {
            $order = Order::create([
                'customer_id' => $user->customer_id,
                'status' => 'new',
                'delivery_address' => $validated['delivery_address'],
                'note' => $validated['note'] ?? null,
                'total_amount' => 0,
            ]);

            $dishIds = collect($validated['items'])->pluck('dish_id')->unique()->values();
            $dishes = Dish::query()->whereIn('id', $dishIds)->get()->keyBy('id');
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $dish = $dishes->get($item['dish_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $dish->price;
                $totalAmount += $unitPrice * $quantity;

                $order->items()->create([
                    'dish_id' => $dish->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            $order->update(['total_amount' => round($totalAmount, 2)]);

            return $order;
        });

        return response()->json($order->load('items.dish'), 201);
    }
}
