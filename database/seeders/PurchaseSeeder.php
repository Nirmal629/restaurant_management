<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /** Mirrors resources/js/purchases/demo-data.js PURCHASE_ORDERS + GOODS_RECEIPTS. */
    public function run(): void
    {
        $sourav = Employee::where('employee_code', 'EMP-009')->first();
        $rakesh = Employee::where('employee_code', 'EMP-006')->first();

        $orders = [
            ['code' => 'PO-2026-0084', 'sup' => 'Bengal Food Supplies', 'date' => '2026-08-23', 'delivery' => '2026-08-25', 'status' => 'approved', 'approved' => true, 'discount' => 0, 'other' => 200,
                'lines' => [['Basmati Rice', 42, 50, 'KG', 95, 5], ['Cooking Oil', 22, 20, 'LITRE', 145, 5], ['Flour', 30, 25, 'KG', 42, 5]]],
            ['code' => 'PO-2026-0083', 'sup' => 'Fresh Chicken Traders', 'date' => '2026-08-22', 'delivery' => '2026-08-23', 'status' => 'partially_received', 'approved' => true, 'discount' => 500, 'other' => 0,
                'lines' => [['Chicken', 8, 30, 'KG', 210, 0], ['Mutton', 14, 10, 'KG', 620, 0]]],
            ['code' => 'PO-2026-0082', 'sup' => 'Kolkata Vegetable Market', 'date' => '2026-08-21', 'delivery' => '2026-08-22', 'status' => 'received', 'approved' => true, 'discount' => 0, 'other' => 100,
                'lines' => [['Onion', 3, 30, 'KG', 32, 0], ['Tomato', 18, 20, 'KG', 28, 0]]],
            ['code' => 'PO-2026-0081', 'sup' => 'Eastern Beverage Distributors', 'date' => '2026-08-19', 'delivery' => '2026-08-21', 'status' => 'ordered', 'approved' => true, 'discount' => 0, 'other' => 0,
                'lines' => [['Coke', 48, 96, 'BOTTLE', 38, 12]]],
            ['code' => 'PO-2026-0080', 'sup' => 'Bengal Food Supplies', 'date' => '2026-08-18', 'delivery' => '2026-08-20', 'status' => 'approval_pending', 'approved' => false, 'discount' => 0, 'other' => 0,
                'lines' => [['Egg', 0, 240, 'PCS', 6, 0]]],
            ['code' => 'PO-2026-0079', 'sup' => 'Kolkata Vegetable Market', 'date' => '2026-08-15', 'delivery' => '2026-08-16', 'status' => 'cancelled', 'approved' => false, 'discount' => 0, 'other' => 0,
                'lines' => [['Tomato', 5, 15, 'KG', 30, 0]]],
        ];

        foreach ($orders as $data) {
            $po = PurchaseOrder::firstOrCreate(
                ['code' => $data['code']],
                [
                    'supplier_id' => Supplier::where('name', $data['sup'])->value('id'),
                    'date' => $data['date'], 'expected_delivery' => $data['delivery'], 'status' => $data['status'],
                    'discount' => $data['discount'], 'other_charges' => $data['other'],
                    'created_by' => $sourav?->id, 'approved_by' => $data['approved'] ? $rakesh?->id : null,
                ]
            );
            foreach ($data['lines'] as [$ingName, $stockSnap, $qty, $unit, $rate, $tax]) {
                $ingredientId = Ingredient::where('name', $ingName)->value('id');
                if ($ingredientId) {
                    $po->lines()->firstOrCreate(
                        ['ingredient_id' => $ingredientId],
                        ['current_stock_snapshot' => $stockSnap, 'qty' => $qty, 'unit' => $unit, 'rate' => $rate, 'tax_pct' => $tax]
                    );
                }
            }
        }

        $receipts = [
            ['code' => 'GRN-2026-0042', 'po' => 'PO-2026-0083', 'invoice' => 'INV-FCT-9981', 'date' => '2026-08-23',
                'lines' => [['Chicken', 30, 0, 20, 0], ['Mutton', 10, 0, 10, 0]]],
            ['code' => 'GRN-2026-0041', 'po' => 'PO-2026-0082', 'invoice' => 'INV-KVM-3312', 'date' => '2026-08-22',
                'lines' => [['Onion', 30, 0, 28, 2], ['Tomato', 20, 0, 20, 0]]],
        ];
        foreach ($receipts as $data) {
            $poId = PurchaseOrder::where('code', $data['po'])->value('id');
            if (! $poId) {
                continue;
            }
            $grn = GoodsReceipt::firstOrCreate(
                ['code' => $data['code']],
                ['purchase_order_id' => $poId, 'invoice_number' => $data['invoice'], 'received_date' => $data['date']]
            );
            foreach ($data['lines'] as [$ingName, $ordered, $prevReceived, $receivedNow, $rejected]) {
                $ingredientId = Ingredient::where('name', $ingName)->value('id');
                if ($ingredientId) {
                    $grn->lines()->firstOrCreate(
                        ['ingredient_id' => $ingredientId],
                        ['ordered_qty' => $ordered, 'previously_received_qty' => $prevReceived, 'received_now_qty' => $receivedNow, 'rejected_qty' => $rejected]
                    );
                }
            }
        }
    }
}
