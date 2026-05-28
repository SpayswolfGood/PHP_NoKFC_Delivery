<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDishController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Dish::query()->with('ingredients')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'price'            => ['required', 'numeric', 'min:0.01'],
            'is_active'        => ['sometimes', 'boolean'],
            'preparation_time' => ['required', 'integer', 'min:1'],
            'image'            => ['nullable', 'image', 'max:4096'],
            'ingredients'      => ['nullable', 'json'], 
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('dishes', 'public');
        }
        unset($validated['image']);

        $dish = Dish::create($validated);

        if ($request->filled('ingredients')) {
            $ingredients = json_parse_ingredients($request->input('ingredients'));
            $dish->ingredients()->sync($ingredients);
        }

        return response()->json($dish->load('ingredients'), 201);
    }

    public function update(Request $request, Dish $dish): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'price'            => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'is_active'        => ['sometimes', 'boolean'],
            'preparation_time' => ['sometimes', 'required', 'integer', 'min:1'],
            'image'            => ['nullable', 'image', 'max:4096'],
            'ingredients'      => ['nullable', 'json'],
        ]);

        if ($request->hasFile('image')) {
            if ($dish->image_path) {
                Storage::disk('public')->delete($dish->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('dishes', 'public');
        }
        unset($validated['image']);

        $dish->update($validated);

        if ($request->has('ingredients')) {
            $ingredients = json_parse_ingredients($request->input('ingredients'));
            $dish->ingredients()->sync($ingredients);
        }

        return response()->json($dish->fresh()->load('ingredients'));
    }

    public function destroy(Dish $dish): JsonResponse
    {
        if ($dish->image_path) {
            Storage::disk('public')->delete($dish->image_path);
        }
        $dish->delete();
        return response()->json(['success' => true]);
    }


    public function getIngredients(): JsonResponse
    {
        return response()->json(Ingredient::query()->orderBy('name')->get());
    }

    public function storeIngredient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0'
        ]);
        $ingredient = Ingredient::create($validated);
        return response()->json($ingredient, 201);
    }

    public function updateIngredient(Request $request, $id): JsonResponse
    {
        $ingredient = Ingredient::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0'
        ]);
        $ingredient->update($validated);
        return response()->json($ingredient);
    }

    public function destroyIngredient($id): JsonResponse
    {
        Ingredient::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}

function json_parse_ingredients($jsonString) {
    $data = json_decode($jsonString, true) ?? [];
    $syncData = [];
    foreach ($data as $item) {
        if (!empty($item['id'])) {
            $syncData[$item['id']] = ['amount' => $item['amount'] ?? 1];
        }
    }
    return $syncData;
}