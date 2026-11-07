<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Restaurant $restaurant;
    private RestaurantTable $table2Seats;
    private RestaurantTable $table4Seats;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@example.com',
        ]);

        $this->restaurant = Restaurant::create([
            'name' => 'NO_KFC Центральный',
            'address' => 'ул. Ленина, 45',
            'phone' => '+7 (495) 100-20-31',
        ]);

        $this->table2Seats = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'table_number' => '1',
            'seats' => 2,
            'is_active' => true,
        ]);

        $this->table4Seats = RestaurantTable::create([
            'restaurant_id' => $this->restaurant->id,
            'table_number' => '2',
            'seats' => 4,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_get_restaurants(): void
    {
        $response = $this->actingAs($this->customer)->getJson('/app-api/customer/restaurants');

        $response->assertOk()
            ->assertJsonFragment(['address' => 'ул. Ленина, 45']);
    }

    public function test_customer_can_see_available_tables_filtered_by_guests(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Check for 3 guests - 2-seat table should be flagged as too_small, 4-seat table available
        $response = $this->actingAs($this->customer)->getJson(
            "/app-api/customer/tables/available?restaurant_id={$this->restaurant->id}&reservation_date={$tomorrow}&start_time=18:00&end_time=20:00&guest_count=3"
        );

        $response->assertOk();
        $data = $response->json();

        $table1 = collect($data)->firstWhere('table_number', '1');
        $table2 = collect($data)->firstWhere('table_number', '2');

        $this->assertTrue($table1['too_small']);
        $this->assertFalse($table1['is_available']);

        $this->assertFalse($table2['too_small']);
        $this->assertTrue($table2['is_available']);
    }

    public function test_customer_can_reserve_table_successfully(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->customer)->postJson('/app-api/customer/reservations', [
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 3,
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
            'note' => 'Юбилей',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', Reservation::STATUS_CONFIRMED)
            ->assertJsonPath('guest_count', 3);

        $this->assertDatabaseHas('reservations', [
            'restaurant_table_id' => $this->table4Seats->id,
            'user_id' => $this->customer->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 3,
            'status' => Reservation::STATUS_CONFIRMED,
        ]);
    }

    public function test_cannot_reserve_table_if_guests_exceed_capacity(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->customer)->postJson('/app-api/customer/reservations', [
            'restaurant_table_id' => $this->table2Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 3, // Table has 2 seats!
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_count']);
    }

    public function test_another_user_cannot_reserve_occupied_table_in_overlapping_time(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Customer 1 books 18:00 - 20:00
        Reservation::create([
            'user_id' => $this->customer->id,
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 4,
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $customer2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Customer 2 attempts overlapping slot 19:00 - 21:00
        $response = $this->actingAs($customer2)->postJson('/app-api/customer/reservations', [
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '19:00',
            'end_time' => '21:00',
            'guest_count' => 2,
            'contact_name' => 'Петр Петров',
            'contact_phone' => '+79992223344',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['restaurant_table_id']);

        // Check availability endpoint also marks it occupied
        $avail = $this->actingAs($customer2)->getJson(
            "/app-api/customer/tables/available?restaurant_id={$this->restaurant->id}&reservation_date={$tomorrow}&start_time=19:00&end_time=21:00&guest_count=2"
        );

        $table2Data = collect($avail->json())->firstWhere('table_number', '2');
        $this->assertTrue($table2Data['is_occupied']);
        $this->assertFalse($table2Data['is_available']);
    }

    public function test_can_reserve_same_table_at_non_overlapping_time(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Existing reservation: 18:00 - 20:00
        Reservation::create([
            'user_id' => $this->customer->id,
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 4,
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $customer2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Customer 2 books non-overlapping slot: 20:00 - 22:00
        $response = $this->actingAs($customer2)->postJson('/app-api/customer/reservations', [
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '20:00',
            'end_time' => '22:00',
            'guest_count' => 2,
            'contact_name' => 'Петр Петров',
            'contact_phone' => '+79992223344',
        ]);

        $response->assertCreated();
    }

    public function test_cancelling_reservation_frees_up_the_table(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $res = Reservation::create([
            'user_id' => $this->customer->id,
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 4,
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $cancelResponse = $this->actingAs($this->customer)
            ->patchJson("/app-api/customer/reservations/{$res->id}/cancel");

        $cancelResponse->assertOk()
            ->assertJsonPath('reservation.status', Reservation::STATUS_CANCELLED);

        $customer2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Now table is available for 18:00 - 20:00
        $response = $this->actingAs($customer2)->postJson('/app-api/customer/reservations', [
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 4,
            'contact_name' => 'Петр Петров',
            'contact_phone' => '+79992223344',
        ]);

        $response->assertCreated();
    }

    public function test_admin_can_manage_reservations_and_tables(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $res = Reservation::create([
            'user_id' => $this->customer->id,
            'restaurant_table_id' => $this->table4Seats->id,
            'reservation_date' => $tomorrow,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 4,
            'contact_name' => 'Иван Иванов',
            'contact_phone' => '+79991112233',
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        // Admin lists reservations
        $response = $this->actingAs($this->admin)->getJson('/app-api/admin/reservations');
        $response->assertOk()->assertJsonCount(1);

        // Admin adds a new table with 6 seats
        $tableRes = $this->actingAs($this->admin)->postJson('/app-api/admin/tables', [
            'restaurant_id' => $this->restaurant->id,
            'table_number' => '10',
            'seats' => 6,
        ]);
        $tableRes->assertCreated();

        // Admin cannot add table with invalid seats (e.g. 7 seats or 1 seat)
        $invalidSeats = $this->actingAs($this->admin)->postJson('/app-api/admin/tables', [
            'restaurant_id' => $this->restaurant->id,
            'table_number' => '11',
            'seats' => 7,
        ]);
        $invalidSeats->assertStatus(422);
    }
}
