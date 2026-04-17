<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['customer', 'items.dish'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateOrder($request);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'status' => $validated['status'] ?? 'new',
                'delivery_address' => $validated['delivery_address'],
                'delivery_time' => $validated['delivery_time'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            $this->syncItemsAndRecalculate($order, $validated['items']);

            return $order;
        });

        return response()->json($order->load(['customer', 'items.dish']), 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['customer', 'items.dish']));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $this->validateOrder($request, true);

        DB::transaction(function () use ($validated, $order) {
            $order->update([
                'customer_id' => $validated['customer_id'] ?? $order->customer_id,
                'status' => $validated['status'] ?? $order->status,
                'delivery_address' => $validated['delivery_address'] ?? $order->delivery_address,
                'delivery_time' => array_key_exists('delivery_time', $validated) ? $validated['delivery_time'] : $order->delivery_time,
                'note' => array_key_exists('note', $validated) ? $validated['note'] : $order->note,
            ]);

            if (array_key_exists('items', $validated)) {
                $this->syncItemsAndRecalculate($order, $validated['items']);
            }
        });

        return response()->json($order->fresh()->load(['customer', 'items.dish']));
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(null, 204);
    }

    private function validateOrder(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'customer_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:customers,id'],
            'status' => ['sometimes', 'string', Rule::in(Order::STATUSES)],
            'delivery_address' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'delivery_time' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'items' => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'items.*.dish_id' => ['required_with:items', 'integer', 'exists:dishes,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ]);
    }

    private function syncItemsAndRecalculate(Order $order, array $items): void
    {
        $order->items()->delete();

        $dishIds = collect($items)->pluck('dish_id')->unique()->values();
        $dishes = Dish::query()->whereIn('id', $dishIds)->get()->keyBy('id');

        $totalAmount = 0;

        foreach ($items as $item) {
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

        $order->update([
            'total_amount' => round($totalAmount, 2),
        ]);
    }
}
