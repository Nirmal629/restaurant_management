<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableReservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_operations_are_dynamic_and_connected(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Orders', 'View'],
            ['Orders', 'Create'],
            ['Orders', 'Edit'],
        ]);

        $floor = Floor::create(['name' => 'Ground Floor', 'display_order' => 1, 'is_active' => true]);
        $table = RestaurantTable::create([
            'floor_id' => $floor->id,
            'code' => 'T99',
            'seats' => 4,
            'shape' => 'square',
            'status' => 'available',
        ]);

        $this->get('/tables')
            ->assertOk()
            ->assertSee('window.tablesModule');

        $this->postJson("/tables/{$table->id}/reserve", [
            'customer' => 'Reservation Guest',
            'phone' => '9830000000',
            'date' => now()->toDateString(),
            'time' => '8:00 PM',
            'guests' => 3,
        ])->assertCreated()
            ->assertJsonPath('tables.0.status', 'reserved');

        $this->assertDatabaseHas('reservations', [
            'customer_name' => 'Reservation Guest',
            'table_id' => $table->id,
            'status' => 'confirmed',
            'time' => '20:00',
        ]);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'reserved']);

        $this->getJson('/tables/data')
            ->assertOk()
            ->assertJsonPath('tables.0.reservationCustomer', 'Reservation Guest')
            ->assertJsonPath('tables.0.reservationGuests', 3)
            ->assertJsonPath('tables.0.reservationTime', '20:00');

        $this->postJson("/tables/{$table->id}/start", [
            'guests' => 3,
            'customer' => 'Reservation Guest',
            'phone' => '9830000000',
        ])->assertCreated()
            ->assertJsonPath('tables.0.status', 'occupied')
            ->assertJsonStructure(['redirect']);

        $this->assertDatabaseHas('orders', [
            'table_id' => $table->id,
            'guests' => 3,
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'occupied']);

        $this->patchJson("/tables/{$table->id}/status", ['status' => 'available'])
            ->assertUnprocessable();

        Order::where('table_id', $table->id)->update(['status' => 'completed']);

        $this->patchJson("/tables/{$table->id}/status", ['status' => 'cleaning'])
            ->assertOk()
            ->assertJsonPath('tables.0.status', 'cleaning');

        $this->putJson("/tables/{$table->id}", [
            'name' => 'Window Table',
            'floor' => 'ground-floor',
            'seats' => 6,
            'shape' => 'round',
            'active' => true,
        ])->assertOk()
            ->assertJsonPath('tables.0.seats', 6);

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'name' => 'Window Table',
            'seats' => 6,
            'shape' => 'round',
        ]);
    }

    public function test_reservation_lifecycle_seats_guest_and_updates_table(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Orders', 'View'],
            ['Orders', 'Create'],
            ['Orders', 'Edit'],
        ]);

        $floor = Floor::create(['name' => 'Ground Floor', 'display_order' => 1, 'is_active' => true]);
        $table = RestaurantTable::create([
            'floor_id' => $floor->id,
            'code' => 'T88',
            'seats' => 4,
            'shape' => 'square',
            'status' => 'available',
        ]);

        $this->get('/reservations')
            ->assertOk()
            ->assertSee('window.reservationsModule');

        $response = $this->postJson('/reservations', [
            'customer' => 'Priya Test',
            'phone' => '9831111111',
            'email' => 'priya@example.com',
            'date' => now()->toDateString(),
            'time' => '20:00',
            'guests' => 4,
            'floor' => 'ground-floor',
            'table' => 'T88',
            'occasion' => 'Birthday',
            'request' => 'Window side',
            'source' => 'Phone',
        ])->assertCreated()
            ->assertJsonPath('reservation.customer', 'Priya Test');

        $reservation = Reservation::where('phone', '9831111111')->firstOrFail();
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'reserved']);

        $this->patchJson("/reservations/{$reservation->id}/status", ['status' => 'confirmed'])
            ->assertOk();
        $this->patchJson("/reservations/{$reservation->id}/status", ['status' => 'arrived'])
            ->assertOk();

        $this->postJson("/reservations/{$reservation->id}/seat", ['table' => 'T88'])
            ->assertOk()
            ->assertJsonStructure(['redirect']);

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'seated']);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('orders', ['table_id' => $table->id, 'status' => 'open']);

        $otherTable = RestaurantTable::create([
            'floor_id' => $floor->id,
            'code' => 'T77',
            'seats' => 2,
            'shape' => 'square',
            'status' => 'available',
        ]);
        $cancel = Reservation::create([
            'code' => 'RES-CANCEL',
            'customer_name' => 'Cancel Guest',
            'phone' => '9832222222',
            'date' => now()->toDateString(),
            'time' => '21:00',
            'guests' => 2,
            'floor_id' => $floor->id,
            'table_id' => $otherTable->id,
            'status' => 'confirmed',
            'source' => 'Phone',
        ]);
        $otherTable->update(['status' => 'reserved']);

        $this->patchJson("/reservations/{$cancel->id}/status", [
            'status' => 'cancelled',
            'reason' => 'Guest called',
        ])->assertOk();

        $this->assertDatabaseHas('reservations', ['id' => $cancel->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $otherTable->id, 'status' => 'available']);
    }
}
