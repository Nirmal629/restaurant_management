<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'WELCOME10', 'name' => 'Welcome Offer', 'type' => 'percent', 'value' => 10, 'min_bill_amount' => 500, 'max_discount_amount' => 250, 'walkin_allowed' => true],
            ['code' => 'FESTIVE15', 'name' => 'Festive Dining Offer', 'type' => 'percent', 'value' => 15, 'min_bill_amount' => 1000, 'max_discount_amount' => 500, 'walkin_allowed' => true],
        ] as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon + ['active' => true, 'per_customer_limit' => 1]
            );
        }
    }
}
