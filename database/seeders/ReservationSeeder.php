<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Floor;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReservationSeeder extends Seeder
{
    /** Representative slice of resources/js/reservations/demo-data.js RESERVATIONS, dates shifted to "today". */
    public function run(): void
    {
        $today = Carbon::today();
        $rakesh = Employee::where('employee_code', 'EMP-006')->first();
        $floorMap = [
            'ground' => 'Ground Floor', 'first' => 'First Floor', 'outdoor' => 'Outdoor', 'vip' => 'VIP Section',
        ];

        $rows = [
            ['customer' => 'Amit Roy', 'phone' => '9830112245', 'offset' => 0, 'time' => '13:00', 'guests' => 4, 'floor' => 'ground', 'table' => 'T01', 'status' => 'completed'],
            ['customer' => 'Priya Das', 'phone' => '9007556621', 'offset' => 0, 'time' => '13:30', 'guests' => 2, 'floor' => 'ground', 'table' => 'T06', 'status' => 'completed'],
            ['customer' => 'Rahul Sen', 'phone' => '9836774410', 'offset' => 0, 'time' => '18:30', 'guests' => 3, 'floor' => 'first', 'table' => 'T11', 'status' => 'seated'],
            ['customer' => 'Nirmal Chakraborty', 'phone' => '9830112244', 'offset' => 0, 'time' => '19:00', 'guests' => 4, 'floor' => 'ground', 'table' => 'T07', 'status' => 'seated', 'request' => 'Window side seating'],
            ['customer' => 'Arjun Sen', 'phone' => '9748899001', 'offset' => 0, 'time' => '19:15', 'guests' => 6, 'floor' => 'vip', 'table' => 'V03', 'status' => 'seated', 'occasion' => 'Business Meet'],
            ['customer' => 'Amit Roy', 'phone' => '9830112245', 'offset' => 0, 'time' => '19:30', 'guests' => 6, 'floor' => 'ground', 'table' => 'T03', 'status' => 'confirmed', 'occasion' => 'Birthday Dinner', 'request' => 'Cake arranged, please bring after mains', 'deposit' => 500],
            ['customer' => 'S. Sen', 'phone' => '9830011223', 'offset' => 0, 'time' => '19:45', 'guests' => 4, 'floor' => 'first', 'status' => 'confirmed'],
            ['customer' => 'K. Iyer', 'phone' => '9748899001', 'offset' => 0, 'time' => '20:15', 'guests' => 5, 'floor' => 'first', 'status' => 'confirmed', 'request' => 'Prefers window side'],
            ['customer' => 'F. Ali', 'phone' => '9903448821', 'offset' => 0, 'time' => '20:00', 'guests' => 4, 'floor' => 'outdoor', 'status' => 'confirmed'],
            ['customer' => 'Sarkar Family', 'phone' => '9007556621', 'offset' => 0, 'time' => '21:00', 'guests' => 8, 'floor' => 'vip', 'status' => 'confirmed', 'occasion' => 'Anniversary', 'request' => 'Cake arranged', 'deposit' => 1000],
            ['customer' => 'Roy Chowdhury', 'phone' => '9830022888', 'offset' => 0, 'time' => '19:20', 'guests' => 5, 'floor' => 'vip', 'status' => 'arrived'],
            ['customer' => 'Walk-in Guest', 'phone' => '9830033777', 'offset' => 0, 'time' => '18:00', 'guests' => 2, 'floor' => 'ground', 'status' => 'arrived', 'source' => 'Walk-in'],
            ['customer' => 'B. Chatterjee', 'phone' => '9830044666', 'offset' => 0, 'time' => '18:00', 'guests' => 3, 'floor' => 'first', 'status' => 'no_show'],
            ['customer' => 'Imtiaz Rahman', 'phone' => '9836774410', 'offset' => 1, 'time' => '20:00', 'guests' => 4, 'floor' => 'vip', 'status' => 'confirmed', 'occasion' => 'Anniversary', 'source' => 'Website'],
            ['customer' => 'Corporate — Tata Steel', 'phone' => '9830022111', 'offset' => 2, 'time' => '20:00', 'guests' => 12, 'floor' => 'ground', 'status' => 'confirmed', 'occasion' => 'Business Meet', 'deposit' => 2000],
        ];

        foreach ($rows as $i => $row) {
            $date = $today->copy()->addDays($row['offset']);
            $floor = Floor::where('name', $floorMap[$row['floor']])->first();
            $table = isset($row['table']) ? RestaurantTable::where('code', $row['table'])->first() : null;
            $customer = Customer::where('phone', $row['phone'])->first();

            $reservation = Reservation::firstOrCreate(
                ['code' => 'RES-' . (204 + $i)],
                [
                    'customer_id' => $customer?->id,
                    'customer_name' => $row['customer'],
                    'phone' => $row['phone'],
                    'date' => $date,
                    'time' => $row['time'],
                    'guests' => $row['guests'],
                    'floor_id' => $floor?->id,
                    'table_id' => $table?->id,
                    'status' => $row['status'],
                    'occasion' => $row['occasion'] ?? 'None',
                    'special_request' => $row['request'] ?? null,
                    'source' => $row['source'] ?? 'Phone',
                    'deposit' => $row['deposit'] ?? 0,
                    'created_by' => $rakesh?->id,
                ]
            );

            if ($reservation->wasRecentlyCreated) {
                $reservation->logActivity('Reservation created via ' . $reservation->source);
            }
        }
    }
}
