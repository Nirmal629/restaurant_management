<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Floor;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class FloorAndTableSeeder extends Seeder
{
    /** Mirrors resources/js/tables/demo-data.js FLOORS + TABLES exactly (28 tables). */
    public function run(): void
    {
        $branch = Branch::where('code', 'ICH-01')->first();

        $floors = [];
        foreach ([['ground', 'Ground Floor'], ['first', 'First Floor'], ['outdoor', 'Outdoor'], ['vip', 'VIP Section']] as $i => [$key, $label]) {
            $floors[$key] = Floor::firstOrCreate(['name' => $label], ['branch_id' => $branch?->id, 'display_order' => $i]);
        }

        $tables = [
            ['T01', 'ground', 4, 'square'], ['T02', 'ground', 4, 'square'], ['T03', 'ground', 6, 'rect'], ['T04', 'ground', 2, 'round'],
            ['T05', 'ground', 4, 'square'], ['T06', 'ground', 2, 'round'], ['T07', 'ground', 6, 'rect'], ['T08', 'ground', 6, 'rect'],
            ['T09', 'ground', 4, 'square'], ['T10', 'ground', 8, 'rect'],
            ['T11', 'first', 4, 'square'], ['T12', 'first', 4, 'square'], ['T13', 'first', 6, 'rect'], ['T14', 'first', 2, 'round'],
            ['T15', 'first', 8, 'rect'], ['T16', 'first', 4, 'square'], ['T17', 'first', 6, 'rect'], ['T18', 'first', 4, 'square'],
            ['T19', 'outdoor', 4, 'square'], ['T20', 'outdoor', 6, 'rect'], ['T21', 'outdoor', 4, 'square'], ['T22', 'outdoor', 4, 'square'],
            ['T23', 'outdoor', 6, 'rect'], ['T24', 'outdoor', 2, 'round'],
            ['V01', 'vip', 6, 'rect'], ['V02', 'vip', 8, 'rect'], ['V03', 'vip', 6, 'rect'], ['V04', 'vip', 4, 'square'],
        ];

        foreach ($tables as [$code, $floorKey, $seats, $shape]) {
            RestaurantTable::firstOrCreate(
                ['code' => $code],
                ['floor_id' => $floors[$floorKey]->id, 'seats' => $seats, 'shape' => $shape, 'status' => 'available']
            );
        }
    }
}
