<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $customer = Customer::query()->updateOrCreate(
            ['phone' => '+79991234567'],
            [
                'name' => 'Ivan Petrov',
                'address' => 'Lenina st, 15',
                'comment' => 'Please call 5 minutes before arrival.',
            ]
        );

        $burger = Dish::query()->updateOrCreate(
            ['name' => 'NOKFC Chicken Burger'],
            [
                'description' => 'Signature burger with crispy chicken.',
                'price' => 320,
                'is_active' => true,
            ]
        );

        $fries = Dish::query()->updateOrCreate(
            ['name' => 'French Fries'],
            [
                'description' => 'Classic french fries.',
                'price' => 150,
                'is_active' => true,
            ]
        );

        DB::transaction(function () use ($customer, $burger, $fries): void {
            $order = Order::query()->firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'status' => 'new',
                    'note' => 'No onions.',
                ],
                [
                    'delivery_address' => $customer->address,
                    'total_amount' => 790,
                ]
            );

            $order->update([
                'delivery_address' => $customer->address,
                'total_amount' => 790,
            ]);

            $order->items()->delete();

            $order->items()->createMany([
                [
                    'dish_id' => $burger->id,
                    'quantity' => 2,
                    'unit_price' => $burger->price,
                ],
                [
                    'dish_id' => $fries->id,
                    'quantity' => 1,
                    'unit_price' => $fries->price,
                ],
            ]);
        });
    }
}
