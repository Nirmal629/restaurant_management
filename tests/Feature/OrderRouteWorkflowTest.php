<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRouteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_route_tracks_kitchen_and_billing_flow(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Orders', 'View'],
            ['Orders', 'Edit'],
        ]);

        $order = $this->createOrderWithItem('ORD-FLOW-001', 'sent');
        $item = $order->items()->firstOrFail();

        $this->get('/orders')
            ->assertOk()
            ->assertSee('window.ordersModule', false)
            ->assertSee('ORD-FLOW-001');

        $this->getJson('/orders/data')
            ->assertOk()
            ->assertJsonPath('orders.0.code', 'ORD-FLOW-001')
            ->assertJsonPath('orders.0.kitchenOpen', 1);

        $this->patchJson("/orders/{$order->id}/status", ['status' => 'billing'])
            ->assertUnprocessable();

        $this->patchJson("/orders/items/{$item->id}/status", ['status' => 'preparing'])
            ->assertOk()
            ->assertJsonPath('order.items.0.status', 'preparing');

        $this->patchJson("/orders/items/{$item->id}/status", ['status' => 'ready'])
            ->assertOk()
            ->assertJsonPath('order.items.0.status', 'ready');

        $this->patchJson("/orders/items/{$item->id}/status", ['status' => 'served'])
            ->assertOk()
            ->assertJsonPath('order.kitchenOpen', 0);

        $this->patchJson("/orders/{$order->id}/status", ['status' => 'billing'])
            ->assertOk()
            ->assertJsonPath('order.status', 'billing');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'billing']);
    }

    private function createOrderWithItem(string $code, string $status): Order
    {
        $category = MenuCategory::firstOrCreate(['name' => 'Mains'], ['display_order' => 1]);
        $menuItem = MenuItem::firstOrCreate(
            ['sku' => $code . '-ITEM'],
            ['name' => 'Order Flow Curry', 'menu_category_id' => $category->id, 'diet_type' => 'veg', 'base_price' => 180]
        );

        $order = Order::create([
            'code' => $code,
            'type' => 'dinein',
            'guests' => 2,
            'status' => 'open',
            'started_at' => now()->subMinutes(8),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'qty' => 1,
            'unit_price' => 180,
            'status' => $status,
            'sent_at' => now()->subMinutes(7),
        ]);

        return $order;
    }
}
