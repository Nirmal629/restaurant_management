<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KdsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kds_loads_real_tickets_and_updates_kitchen_statuses(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Kitchen', 'View'],
            ['Kitchen', 'Edit'],
            ['Orders', 'View'],
        ]);

        $category = MenuCategory::create(['name' => 'Mains', 'display_order' => 1]);
        $menuItem = MenuItem::create([
            'sku' => 'KDS-ITEM',
            'name' => 'KDS Curry',
            'menu_category_id' => $category->id,
            'diet_type' => 'veg',
            'base_price' => 120,
            'availability' => 'available',
        ]);
        $order = Order::create([
            'code' => 'ORD-KDS-001',
            'type' => 'dinein',
            'guests' => 2,
            'status' => 'open',
            'started_at' => now()->subMinutes(5),
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'qty' => 2,
            'unit_price' => 120,
            'kot_round' => 1,
            'status' => 'sent',
            'sent_at' => now()->subMinutes(4),
        ]);

        $this->get('/kds')
            ->assertOk()
            ->assertSee('window.kdsModule')
            ->assertSee('KDS Curry');

        $this->getJson('/kds/data')
            ->assertOk()
            ->assertJsonPath('tickets.0.orderCode', 'ORD-KDS-001')
            ->assertJsonPath('tickets.0.status', 'new');

        $this->patchJson("/kds/orders/{$order->id}/status", ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonPath('tickets.0.status', 'accepted');
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'status' => 'accepted']);

        $this->patchJson("/kds/items/{$item->id}/status", ['status' => 'ready'])
            ->assertOk()
            ->assertJsonPath('tickets.0.status', 'ready');
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'status' => 'ready']);

        $this->patchJson("/kds/orders/{$order->id}/status", ['status' => 'served'])
            ->assertOk()
            ->assertJsonCount(0, 'tickets');
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'status' => 'served']);
    }
}
