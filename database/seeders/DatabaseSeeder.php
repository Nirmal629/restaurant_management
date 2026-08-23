<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the same restaurant — Royal
     * Bengal Restaurant, Ichapur Main Branch — used throughout every
     * frontend module's demo data, so real records match the designs.
     *
     * Order matters: each seeder here depends on rows created by the ones
     * before it (branch → roles → employees → customers/menu/floors →
     * suppliers → inventory → purchases → reservations/expenses → orders).
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            RoleAndPermissionSeeder::class,
            EmployeeSeeder::class,
            CustomerSeeder::class,
            MenuSeeder::class,
            FloorAndTableSeeder::class,
            SupplierSeeder::class,
            InventorySeeder::class,
            PurchaseSeeder::class,
            ReservationSeeder::class,
            ExpenseSeeder::class,
            OrderAndBillingSeeder::class,
        ]);
    }
}
