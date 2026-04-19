<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_KITCHEN, User::ROLE_COURIER])
                ->latest()
                ->get(['id', 'name', 'email', 'role', 'created_at'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_KITCHEN, User::ROLE_COURIER])],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $customerId = null;
        if (!empty($validated['phone']) && !empty($validated['address'])) {
            $customer = Customer::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'comment' => 'Auto-created for staff account',
            ]);
            $customerId = $customer->id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'customer_id' => $customerId,
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role']), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_KITCHEN, User::ROLE_COURIER])],
        ]);

        if (array_key_exists('password', $validated) && $validated['password'] !== null) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->role === User::ROLE_CUSTOMER) {
            return response()->json(['message' => 'Only staff can be deleted here'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
