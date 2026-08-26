<?php

namespace Tests\Feature;

use App\Models\Employee;
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
        [, $employee] = $this->actingAsEmployeeWithPermissions([
            ['Orders', 'View'],
            ['Orders', 'Create'],
            ['Orders', 'Edit'],
            ['POS', 'View'],
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

        $target = RestaurantTable::create([
            'floor_id' => $floor->id,
            'code' => 'T100',
            'seats' => 4,
            'shape' => 'square',
            'status' => 'available',
        ]);

        $this->postJson("/tables/{$table->id}/transfer", ['to' => 'T100'])
            ->assertOk()
            ->assertJsonFragment(['id' => 'T99', 'status' => 'cleaning'])
            ->assertJsonFragment(['id' => 'T100', 'status' => 'occupied']);

        $this->assertDatabaseHas('orders', [
            'table_id' => $target->id,
            'guests' => 3,
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'cleaning']);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $target->id, 'status' => 'occupied']);

        $emptyStaleOrder = Order::create([
            'code' => 'ORD-STALE-EMPTY',
            'type' => 'dinein',
            'table_id' => $table->id,
            'guests' => 1,
            'status' => 'open',
            'started_at' => now(),
        ]);

        $this->patchJson("/tables/{$table->id}/status", ['status' => 'available'])
            ->assertOk()
            ->assertJsonFragment(['id' => 'T99', 'status' => 'available']);

        $this->assertDatabaseHas('orders', ['id' => $emptyStaleOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'status' => 'available']);

        Order::where('table_id', $target->id)->update(['status' => 'completed']);

        $this->patchJson("/tables/{$target->id}/status", ['status' => 'cleaning'])
            ->assertOk()
            ->assertJsonFragment(['id' => 'T100', 'status' => 'cleaning']);

        $target->refresh()->update(['status' => 'available']);
        $order = Order::create([
            'code' => 'ORD-MERGE-001',
            'type' => 'dinein',
            'table_id' => $table->id,
            'waiter_id' => $employee->id,
            'guests' => 2,
            'status' => 'open',
            'started_at' => now(),
        ]);
        $table->update(['status' => 'occupied']);

        $newWaiter = Employee::create([
            'role_id' => $employee->role_id,
            'branch_id' => $employee->branch_id,
            'employee_code' => 'EMP-WAITER',
            'name' => 'Functional Waiter',
            'phone' => '9888888888',
            'status' => 'active',
        ]);

        $this->patchJson("/tables/{$table->id}/waiter", ['waiter' => 'Functional Waiter'])
            ->assertOk()
            ->assertJsonFragment(['id' => 'T99', 'waiter' => 'Functional Waiter']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'waiter_id' => $newWaiter->id]);

        $this->postJson("/tables/{$table->id}/merge", ['with' => 'T100'])
            ->assertOk()
            ->assertJsonPath('merge.primary', 'T99')
            ->assertJsonPath('merge.secondary', 'T100')
            ->assertJsonFragment(['id' => 'T99', 'groupPrimary' => true])
            ->assertJsonFragment(['id' => 'T100', 'status' => 'occupied', 'groupPrimary' => false]);

        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id, 'is_merge_primary' => true]);
        $this->assertDatabaseHas('restaurant_tables', ['id' => $target->id, 'merged_with_table_id' => $table->id]);
        $this->getJson('/tables/data')
            ->assertOk()
            ->assertJsonFragment(['id' => 'T99', 'groupPrimary' => true])
            ->assertJsonFragment(['id' => 'T100', 'mergedWithTableId' => $table->id, 'groupPrimary' => false]);

        $this->getJson('/pos/data?order=' . $order->id)
            ->assertOk()
            ->assertJsonPath('activeOrder.table', 'T99 + T100')
            ->assertJsonPath('activeOrder.floor', 'Ground Floor');

        $this->putJson("/tables/{$table->id}", [
            'name' => 'Window Table',
            'floor' => 'ground-floor',
            'seats' => 6,
            'shape' => 'round',
            'active' => true,
        ])->assertOk()
            ->assertJsonFragment(['id' => 'T99', 'seats' => 6]);

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
            'time' => now()->addMinutes(20)->format('H:i'),
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

        $futureTable = RestaurantTable::create([
            'floor_id' => $floor->id,
            'code' => 'T89',
            'seats' => 4,
            'shape' => 'square',
            'status' => 'available',
        ]);
        $this->postJson('/reservations', [
            'customer' => 'Evening Guest',
            'phone' => '9833333333',
            'email' => 'evening@example.com',
            'date' => now()->toDateString(),
            'time' => now()->addHours(5)->format('H:i'),
            'guests' => 4,
            'floor' => 'ground-floor',
            'table' => 'T89',
            'occasion' => 'Dinner',
            'request' => null,
            'source' => 'Phone',
        ])->assertCreated();
        $this->assertDatabaseHas('restaurant_tables', ['id' => $futureTable->id, 'status' => 'available']);

        $this->postJson('/reservations', [
            'customer' => 'Double Book',
            'phone' => '9834444444',
            'email' => 'double@example.com',
            'date' => now()->toDateString(),
            'time' => now()->addHours(5)->addMinutes(30)->format('H:i'),
            'guests' => 2,
            'floor' => 'ground-floor',
            'table' => 'T89',
            'occasion' => 'Dinner',
            'request' => null,
            'source' => 'Phone',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['table']);

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
