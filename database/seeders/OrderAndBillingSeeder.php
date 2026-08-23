<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Kot;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class OrderAndBillingSeeder extends Seeder
{
    /**
     * A single, fully-worked order proving the POS → KOT → Billing chain end
     * to end: mirrors ORD-1028 / INV-2026-001028 from the Billing module's
     * demo data (resources/js/billing/demo-data.js) — same discount,
     * complimentary, cancelled and refunded line states.
     */
    public function run(): void
    {
        $table = RestaurantTable::where('code', 'T08')->first();
        $customer = Customer::where('phone', '9830112244')->first(); // Nirmal Chakraborty
        $waiter = Employee::where('employee_code', 'EMP-001')->first(); // Rahul Das

        $order = Order::firstOrCreate(
            ['code' => 'ORD-1028'],
            [
                'type' => 'dinein', 'table_id' => $table?->id, 'customer_id' => $customer?->id,
                'waiter_id' => $waiter?->id, 'guests' => 4, 'status' => 'billing', 'started_at' => now()->subMinutes(42),
            ]
        );

        if ($order->wasRecentlyCreated) {
            $lines = [
                ['Chicken Biryani', 2, 320, null, [], 'Less Spicy', 'served'],
                ['Mutton Biryani', 1, 420, null, [], 'Extra Gravy', 'served'],
                ['Paneer Tikka', 1, 280, null, [], null, 'served', 'complimentary', 0, 'Owner Courtesy', 'EMP-006'],
                ['Butter Naan', 4, 50, null, [], null, 'served'],
                ['Chicken Tikka', 1, 360, null, [], null, 'served', 'discounted', 60],
                ['Coke', 2, 60, null, [], null, 'served'],
                ['Ice Cream', 2, 120, null, [], null, 'served'],
                ['Mutton Rogan Josh', 1, 450, null, [], null, 'cancelled'],
                ['Coke', 1, 60, null, [], null, 'served', 'refunded', 0, null, null, 'Wrong item served', 60],
            ];

            foreach ($lines as $line) {
                [$name, $qty, $price, $variant, $mods, $note, $status] = $line;
                $billStatus = $line[7] ?? 'normal';
                $billDiscount = $line[8] ?? 0;
                $compReason = $line[9] ?? null;
                $compByCode = $line[10] ?? null;
                $refundReason = $line[11] ?? null;
                $refundAmount = $line[12] ?? 0;

                $menuItem = MenuItem::where('name', $name)->first();
                if (! $menuItem) {
                    continue;
                }

                $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'variant_label' => $variant,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'modifiers' => $mods,
                    'note' => $note,
                    'kitchen_station_id' => $menuItem->kitchen_station_id,
                    'kot_round' => $name === 'Mutton Rogan Josh' ? 2 : 1,
                    'status' => $status,
                    'cancel_reason' => $status === 'cancelled' ? 'Guest changed mind' : null,
                    'bill_status' => $billStatus,
                    'bill_discount_amount' => $billDiscount,
                    'comp_reason' => $compReason,
                    'comp_by' => $compByCode ? Employee::where('employee_code', $compByCode)->value('id') : null,
                    'refund_reason' => $refundReason,
                    'refund_amount' => $refundAmount,
                    'sent_at' => now()->subMinutes(30),
                    'ready_at' => now()->subMinutes(20),
                ]);
            }

            Kot::create(['code' => 'KOT-1024', 'order_id' => $order->id, 'round' => 1, 'printer' => 'Main Kitchen + Tandoor', 'sent_at' => now()->subMinutes(32)]);
            Kot::create(['code' => 'KOT-1045', 'order_id' => $order->id, 'round' => 2, 'printer' => 'Main Kitchen', 'sent_at' => now()->subMinutes(18)]);
        }

        $invoice = Invoice::firstOrCreate(
            ['code' => 'INV-2026-001028'],
            [
                'order_id' => $order->id,
                'status' => 'partially_paid',
                'tax_mode' => 'exclusive',
                'cgst_rate' => 0.025,
                'sgst_rate' => 0.025,
                'service_rate' => 0.05,
                'bill_discount_mode' => 'pct',
                'bill_discount_value' => 12,
                'bill_discount_reason' => 'Customer loyalty',
                'bill_discount_approved_by' => Employee::where('employee_code', 'EMP-006')->value('id'),
            ]
        );

        if ($invoice->payments()->count() === 0) {
            $invoice->payments()->create(['method' => 'cash', 'amount' => 500, 'tendered' => 500, 'paid_at' => now()->subMinutes(8)]);
        }

        // A second, fully-settled order/invoice so Reports and history views have more than one data point.
        $order2 = Order::firstOrCreate(
            ['code' => 'ORD-1019'],
            [
                'type' => 'dinein',
                'table_id' => RestaurantTable::where('code', 'T03')->value('id'),
                'waiter_id' => $waiter?->id,
                'guests' => 5, 'status' => 'paid', 'started_at' => now()->subHours(2),
            ]
        );
        if ($order2->wasRecentlyCreated) {
            $menuItem = MenuItem::where('name', 'Chicken Fried Rice')->first();
            if ($menuItem) {
                $order2->items()->create([
                    'menu_item_id' => $menuItem->id, 'qty' => 3, 'unit_price' => 280,
                    'kitchen_station_id' => $menuItem->kitchen_station_id, 'status' => 'served',
                    'sent_at' => now()->subHours(2), 'ready_at' => now()->subHours(2)->addMinutes(15),
                ]);
            }
        }
        $invoice2 = Invoice::firstOrCreate(['code' => 'INV-2026-001019'], ['order_id' => $order2->id, 'status' => 'paid']);
        if ($invoice2->payments()->count() === 0) {
            $invoice2->payments()->create(['method' => 'upi', 'amount' => $invoice2->grandTotal(), 'reference' => 'UPI23982', 'paid_at' => now()->subHours(2)->addMinutes(20)]);
        }
    }
}
