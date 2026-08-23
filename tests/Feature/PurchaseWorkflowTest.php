<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\StockLedgerEntry;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_can_be_created_approved_and_received(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Purchases', 'Create'],
            ['Purchases', 'Edit'],
            ['Purchases', 'Approve'],
        ]);

        $supplier = Supplier::create(['name' => 'Bengal Food Supplies', 'status' => 'active']);
        $ingredient = Ingredient::create([
            'code' => 'ING-001',
            'name' => 'Basmati Rice',
            'category' => 'Grains',
            'unit' => 'KG',
            'current_stock' => 10,
            'min_stock' => 5,
            'reorder_level' => 8,
            'avg_cost' => 70,
            'supplier_id' => $supplier->id,
        ]);
        PurchaseOrder::create([
            'code' => 'PO-' . now()->format('Y') . '-0084',
            'supplier_id' => $supplier->id,
            'date' => now()->toDateString(),
            'status' => 'cancelled',
        ]);

        $create = $this->postJson('/purchases/orders', [
            'supplier' => $supplier->name,
            'expectedDelivery' => now()->addDay()->toDateString(),
            'reference' => 'REF-1',
            'notes' => 'Weekly rice order',
            'items' => [[
                'ingredient' => $ingredient->name,
                'qty' => 15,
                'unit' => 'KG',
                'rate' => 72,
                'tax' => 5,
            ]],
            'discount' => 10,
            'otherCharges' => 20,
        ]);

        $create->assertCreated()
            ->assertJsonPath('order.id', 'PO-' . now()->format('Y') . '-0085')
            ->assertJsonPath('order.status', 'draft');

        $order = PurchaseOrder::where('code', $create->json('order.id'))->firstOrFail();

        $this->putJson("/purchases/orders/{$order->id}", [
            'supplier' => $supplier->name,
            'expectedDelivery' => now()->addDays(2)->toDateString(),
            'reference' => 'REF-2',
            'notes' => 'Updated weekly rice order',
            'items' => [[
                'ingredient' => $ingredient->name,
                'qty' => 18,
                'unit' => 'KG',
                'rate' => 71,
                'tax' => 5,
            ]],
            'discount' => 0,
            'otherCharges' => 20,
        ])->assertOk()
            ->assertJsonPath('order.reference', 'REF-2')
            ->assertJsonPath('order.items.0.qty', 18);

        $this->patchJson("/purchases/orders/{$order->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('order.status', 'approved');

        $this->patchJson("/purchases/orders/{$order->id}/status", ['status' => 'ordered'])
            ->assertOk()
            ->assertJsonPath('order.status', 'ordered');

        $receipt = $this->postJson('/purchases/receipts', [
            'poRef' => $order->code,
            'invoiceNumber' => 'INV-100',
            'receivedDate' => now()->toDateString(),
            'items' => [[
                'ingredient' => $ingredient->name,
                'ordered' => 18,
                'prevReceived' => 0,
                'receivedNow' => 18,
                'rejected' => 2,
            ]],
        ]);

        $receipt->assertCreated()
            ->assertJsonPath('orders.0.status', 'received');

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'current_stock' => 26]);
        $this->assertDatabaseHas('stock_ledger_entries', [
            'ingredient_id' => $ingredient->id,
            'type' => 'PURCHASE',
            'change_qty' => 16,
        ]);
    }
}
