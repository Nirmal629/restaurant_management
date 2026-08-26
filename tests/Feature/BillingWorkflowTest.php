<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Coupon;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_can_be_paid_completed_refunded_and_voided(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Billing', 'View'],
            ['Billing', 'Create'],
            ['Billing', 'Edit'],
            ['Billing', 'Cancel'],
        ]);

        $order = $this->createBillableOrder('ORD-BILL-001', 2, 100);

        Coupon::create([
            'code' => 'BILL10',
            'name' => 'Billing Test',
            'type' => 'percent',
            'value' => 10,
            'min_bill_amount' => 100,
            'walkin_allowed' => true,
            'active' => true,
        ]);

        $this->get('/billing?order=' . $order->id)
            ->assertOk()
            ->assertSee('BILL10');

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(220, $invoice->fresh()->grandTotal());

        $this->postJson("/billing/invoices/{$invoice->id}/coupon", [
            'code' => 'BILL10',
        ])->assertOk()
            ->assertJsonPath('coupon.code', 'BILL10')
            ->assertJsonPath('totals.couponAmount', 20)
            ->assertJsonPath('totals.grandTotal', 198);

        $this->assertDatabaseHas('coupon_redemptions', [
            'invoice_id' => $invoice->id,
        ]);

        $this->deleteJson("/billing/invoices/{$invoice->id}/coupon")
            ->assertOk()
            ->assertJsonPath('coupon', null)
            ->assertJsonPath('totals.couponAmount', 0)
            ->assertJsonPath('totals.grandTotal', 220);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'coupon_code' => null,
            'coupon_amount' => 0,
        ]);
        $this->assertDatabaseMissing('coupon_redemptions', [
            'invoice_id' => $invoice->id,
        ]);

        $this->postJson("/billing/invoices/{$invoice->id}/coupon", [
            'code' => 'BILL10',
        ])->assertOk()
            ->assertJsonPath('coupon.amount', 20)
            ->assertJsonPath('totals.grandTotal', 198);

        $this->postJson("/billing/invoices/{$invoice->id}/payments", [
            'method' => 'cash',
            'amount' => 198,
            'tendered' => 250,
        ])->assertOk()
            ->assertJsonPath('invoice.status', 'paid');

        $this->deleteJson("/billing/invoices/{$invoice->id}/coupon")
            ->assertOk()
            ->assertJsonPath('coupon', null)
            ->assertJsonPath('totals.couponAmount', 0)
            ->assertJsonPath('totals.grandTotal', 220)
            ->assertJsonPath('totals.paidTotal', 198)
            ->assertJsonPath('totals.dueAmount', 22)
            ->assertJsonPath('invoice.status', 'partially_paid');

        $this->postJson("/billing/invoices/{$invoice->id}/payments", [
            'method' => 'cash',
            'amount' => 22,
            'tendered' => 22,
        ])->assertOk()
            ->assertJsonPath('invoice.status', 'paid');

        $this->postJson("/billing/invoices/{$invoice->id}/complete")
            ->assertOk()
            ->assertJsonPath('invoice.status', 'paid');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);

        $this->postJson("/billing/invoices/{$invoice->id}/refunds", [
            'mode' => 'partial',
            'method' => 'cash',
            'amount' => 50,
            'reason' => 'Guest complaint',
        ])->assertOk()
            ->assertJsonPath('invoice.status', 'partially_refunded');

        $voidOrder = $this->createBillableOrder('ORD-BILL-002', 1, 100);
        $voidInvoice = Invoice::create(['code' => 'INV-TEST-VOID', 'order_id' => $voidOrder->id]);

        $this->postJson("/billing/invoices/{$voidInvoice->id}/void", [
            'reason' => 'Wrong table',
        ])->assertOk()
            ->assertJsonPath('invoice.status', 'voided');
    }

    public function test_billing_exposes_queue_and_preserves_paid_order_status(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Billing', 'View'],
            ['Billing', 'Create'],
            ['Billing', 'Edit'],
        ]);

        $first = $this->createBillableOrder('ORD-QUEUE-001', 1, 100);
        $second = $this->createBillableOrder('ORD-QUEUE-002', 2, 150);

        $this->getJson('/billing/data?order=' . $first->id)
            ->assertOk()
            ->assertJsonPath('order.code', 'ORD-QUEUE-001')
            ->assertJsonCount(2, 'orders');

        $firstInvoice = Invoice::where('order_id', $first->id)->firstOrFail();

        $this->postJson("/billing/invoices/{$firstInvoice->id}/payments", [
            'method' => 'cash',
            'amount' => 110,
            'tendered' => 110,
        ])->assertOk()
            ->assertJsonPath('invoice.status', 'paid');

        $first->update(['status' => 'paid']);

        $this->getJson('/billing/data?order=' . $second->id)
            ->assertOk()
            ->assertJsonPath('order.code', 'ORD-QUEUE-002')
            ->assertJsonCount(1, 'orders');

        $this->getJson('/billing/data')
            ->assertOk()
            ->assertJsonPath('order.code', 'ORD-QUEUE-002')
            ->assertJsonCount(1, 'orders');

        $this->getJson('/billing/data?order=' . $first->id)
            ->assertOk()
            ->assertJsonPath('order.code', 'ORD-QUEUE-001')
            ->assertJsonPath('invoice.status', 'paid')
            ->assertJsonCount(0, 'history');

        $this->assertDatabaseHas('orders', ['id' => $first->id, 'status' => 'paid']);

        $this->postJson("/billing/invoices/{$firstInvoice->id}/close")
            ->assertOk()
            ->assertJsonPath('orders.0.code', 'ORD-QUEUE-002')
            ->assertJsonPath('history.0.code', $firstInvoice->code);

        $this->assertDatabaseHas('orders', ['id' => $first->id, 'status' => 'completed']);
    }

    private function createBillableOrder(string $code, int $qty, int $rate): Order
    {
        $category = MenuCategory::firstOrCreate(['name' => 'Mains'], ['display_order' => 1]);
        $item = MenuItem::firstOrCreate(
            ['sku' => $code . '-ITEM'],
            [
                'name' => 'Test Curry',
                'menu_category_id' => $category->id,
                'diet_type' => 'veg',
                'base_price' => $rate,
            ]
        );

        $order = Order::create([
            'code' => $code,
            'type' => 'dinein',
            'guests' => 2,
            'status' => 'open',
            'started_at' => now()->subMinutes(15),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $item->id,
            'qty' => $qty,
            'unit_price' => $rate,
            'status' => 'served',
            'sent_at' => now()->subMinutes(12),
        ]);

        return $order;
    }
}
