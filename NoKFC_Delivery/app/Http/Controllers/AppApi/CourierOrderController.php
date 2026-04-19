<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierOrderController extends Controller
{
    public function available(): JsonResponse
    {
        return response()->json(
            Order::query()
                ->with(['customer', 'items.dish'])
                ->where('status', 'confirmed')
                ->whereNull('courier_id')
                ->latest()
                ->get()
        );
    }

    public function myOrders(Request $request): JsonResponse
    {
        return response()->json(
            Order::query()
                ->with(['customer', 'items.dish'])
                ->where('courier_id', $request->user()->id)
                ->whereIn('status', ['on_the_way', 'delivered'])
                ->latest()
                ->get()
        );
    }

    public function claim(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'confirmed' || $order->courier_id !== null) {
            return response()->json(['message' => 'Order is not available for courier'], 422);
        }

        $order->update([
            'status' => 'on_the_way',
            'courier_id' => $request->user()->id,
        ]);

        return response()->json($order->fresh()->load(['customer', 'items.dish']));
    }

    public function deliver(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'on_the_way' || $order->courier_id !== $request->user()->id) {
            return response()->json(['message' => 'Only assigned order on the way can be delivered'], 422);
        }

        $order->update(['status' => 'delivered']);

        return response()->json($order->fresh()->load(['customer', 'items.dish']));
    }
}
