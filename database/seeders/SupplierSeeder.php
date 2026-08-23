<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /** Mirrors resources/js/purchases/demo-data.js SUPPLIERS exactly. */
    public function run(): void
    {
        $rows = [
            ['name' => 'Bengal Food Supplies', 'contact' => 'Debashish Pal', 'phone' => '9830011122', 'email' => 'sales@bengalfood.example', 'gstin' => '19AABCB1234K1Z1', 'address' => 'Ichapur Industrial Area, North 24 Parganas', 'outstanding' => 12400],
            ['name' => 'Fresh Chicken Traders', 'contact' => 'Manoj Halder', 'phone' => '9830033344', 'email' => 'orders@freshchicken.example', 'gstin' => '19AABCF5678K1Z2', 'address' => 'Barrackpore Trunk Road, Ichapur', 'outstanding' => 0],
            ['name' => 'Kolkata Vegetable Market', 'contact' => 'Ratan Das', 'phone' => '9830055566', 'email' => null, 'gstin' => null, 'address' => 'Sealdah Wholesale Market, Kolkata', 'outstanding' => 3200],
            ['name' => 'Eastern Beverage Distributors', 'contact' => 'Sanjay Ghosh', 'phone' => '9830077788', 'email' => 'distro@easternbev.example', 'gstin' => '19AABCE9012K1Z3', 'address' => 'VIP Road, Kolkata', 'outstanding' => 0, 'status' => 'inactive'],
        ];

        foreach ($rows as $row) {
            Supplier::firstOrCreate(
                ['name' => $row['name']],
                [
                    'contact_person' => $row['contact'], 'phone' => $row['phone'], 'email' => $row['email'],
                    'gstin' => $row['gstin'], 'address' => $row['address'], 'outstanding' => $row['outstanding'],
                    'status' => $row['status'] ?? 'active',
                ]
            );
        }
    }
}
