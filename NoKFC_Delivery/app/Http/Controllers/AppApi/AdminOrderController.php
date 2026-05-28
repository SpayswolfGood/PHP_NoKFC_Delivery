<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with(['customer', 'courier', 'items.dish'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json(
            $order->load(['customer', 'courier', 'items.dish'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'      => ['required', 'exists:customers,id'],
            'courier_id'       => ['nullable', 'exists:users,id'],
            'status'           => ['sometimes', 'in:' . implode(',', Order::STATUSES)],
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_time'    => ['nullable', 'date'],
            'note'             => ['nullable', 'string'],
            'total_amount'     => ['required', 'numeric', 'min:0'],
        ]);

        $order = Order::create($validated);

        return response()->json($order->load(['customer', 'courier', 'items.dish']), 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'courier_id'       => ['sometimes', 'nullable', 'exists:users,id'],
            'status'           => ['sometimes', 'in:' . implode(',', Order::STATUSES)],
            'delivery_address' => ['sometimes', 'required', 'string', 'max:500'],
            'delivery_time'    => ['sometimes', 'nullable', 'date'],
            'note'             => ['sometimes', 'nullable', 'string'],
            'total_amount'     => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $order->update($validated);

        return response()->json($order->load(['customer', 'courier', 'items.dish']));
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(null, 204);
    }
}