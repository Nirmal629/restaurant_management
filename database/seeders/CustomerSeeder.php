<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /** Mirrors resources/js/customers/demo-data.js CUSTOMERS exactly. */
    public function run(): void
    {
        $rows = [
            ['name' => 'Nirmal Chakraborty', 'phone' => '9830112244', 'email' => 'nirmal.c@example.com', 'points' => 420, 'tags' => ['Regular', 'Foodie'], 'vip' => true, 'birthday' => '1986-08-23'],
            ['name' => 'Sourav Banerjee', 'phone' => '9830112245', 'email' => 'sourav.b@example.com', 'points' => 1240, 'tags' => ['Regular', 'Corporate'], 'vip' => true],
            ['name' => 'Ananya Dutta', 'phone' => '9007556621', 'email' => 'ananya.d@example.com', 'points' => 310, 'tags' => ['Foodie'], 'anniversary' => '2020-11-02'],
            ['name' => 'Imtiaz Rahman', 'phone' => '9836774410', 'email' => 'imtiaz.r@example.com', 'points' => 2680, 'tags' => ['Regular', 'Loyal', 'Corporate'], 'vip' => true, 'gstin' => '19AAAAA0000A1Z5'],
            ['name' => 'Priya Ghosh', 'phone' => '9163302299', 'email' => null, 'points' => 120, 'tags' => []],
            ['name' => 'Rohit Sharma', 'phone' => '9748110034', 'email' => 'rohit.sharma@example.com', 'points' => 690, 'tags' => ['Family'], 'anniversary' => '2026-08-30'],
            ['name' => 'Farhan Ali', 'phone' => '9903448821', 'email' => null, 'points' => 40],
            ['name' => 'Amit Roy', 'phone' => '9830112245', 'email' => 'amit.roy@example.com', 'points' => 190, 'tags' => ['Family'], 'birthday' => '2026-08-28'],
            ['name' => 'Priya Das', 'phone' => '9007556621', 'email' => null, 'points' => 82],
            ['name' => 'Arjun Sen', 'phone' => '9748899001', 'email' => 'arjun.sen@example.com', 'points' => 980, 'tags' => ['Corporate', 'Regular'], 'vip' => true],
            ['name' => 'Rahul Sen', 'phone' => '9836774410', 'email' => null, 'points' => 15],
            ['name' => 'S. Sen', 'phone' => '9830011223', 'email' => null, 'points' => 60],
        ];

        foreach ($rows as $row) {
            Customer::firstOrCreate(
                ['phone' => $row['phone'], 'name' => $row['name']],
                [
                    'email' => $row['email'] ?? null,
                    'birthday' => $row['birthday'] ?? null,
                    'anniversary' => $row['anniversary'] ?? null,
                    'gstin' => $row['gstin'] ?? null,
                    'tags' => $row['tags'] ?? [],
                    'is_vip' => $row['vip'] ?? false,
                    'loyalty_points' => $row['points'],
                    'joined_date' => '2024-01-15',
                ]
            );
        }
    }
}
