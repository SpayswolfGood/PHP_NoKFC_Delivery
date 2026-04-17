<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_order_and_calculates_total_amount(): void
    {
        $customer = Customer::factory()->create();
        $dishA = Dish::factory()->create(['price' => 250]);
        $dishB = Dish::factory()->create(['price' => 100]);

        $payload = [
            'customer_id' => $customer->id,
            'delivery_address' => 'Main street 1',
            'items' => [
                ['dish_id' => $dishA->id, 'quantity' => 2],
                ['dish_id' => $dishB->id, 'quantity' => 3],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('total_amount', '800.00')
            ->assertJsonCount(2, 'items');
    }
}
