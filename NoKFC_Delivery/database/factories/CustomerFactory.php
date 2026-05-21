<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+79'.fake()->numerify('#########'),
            'address' => fake()->streetAddress(),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
