<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenOrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Order::query()
                ->with(['customer', 'items.dish'])
                ->whereIn('status', ['new', 'preparing'])
                ->latest()
                ->get()
        );
    }

    public function setPreparing(Order $order): JsonResponse
    {
        if ($order->status !== 'new') {
            return response()->json(['message' => 'Only new orders can be set to preparing'], 422);
        }

        $order->update(['status' => 'preparing']);
        return response()->json($order->fresh()->load(['customer', 'items.dish']));
    }

    public function setReady(Order $order): JsonResponse
    {
        if ($order->status !== 'preparing') {
            return response()->json(['message' => 'Only preparing orders can be completed'], 422);
        }

        $order->update(['status' => 'confirmed']);
        return response()->json($order->fresh()->load(['customer', 'items.dish']));
    }

    public function ingredients(): JsonResponse
    {
        return response()->json(Ingredient::query()->orderBy('name')->get());
    }

    public function storeIngredient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'is_available' => 'sometimes|boolean'
        ]);

        $ingredient = Ingredient::create($validated);
        return response()->json($ingredient, 201);
    }

    public function updateIngredient(Request $request, $id): JsonResponse
    {
        $ingredient = Ingredient::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0',
            'is_available' => 'sometimes|required|boolean'
        ]);

        $ingredient->update($validated);
        return response()->json($ingredient);
    }

    public function destroyIngredient($id): JsonResponse
    {
        Ingredient::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function toggleIngredient(Ingredient $ingredient, Request $request): JsonResponse
    {
        $request->validate([
            'is_available' => 'required|boolean'
        ]);

        $ingredient->update([
            'is_available' => $request->input('is_available')
        ]);

        return response()->json($ingredient);
    }
}