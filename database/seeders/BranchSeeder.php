<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::firstOrCreate(
            ['code' => 'ICH-01'],
            [
                'name' => 'Ichapur Main Branch',
                'address' => '18 Grand Trunk Road, Ichapur, North 24 Parganas, WB 743144',
                'phone' => '+91 33 2593 4400',
                'opening_time' => '11:00',
                'closing_time' => '23:00',
                'is_active' => true,
            ]
        );
    }
}
