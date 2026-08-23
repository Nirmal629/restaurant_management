<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /** Mirrors resources/js/employees/demo-data.js EMPLOYEES exactly. */
    public function run(): void
    {
        $branch = Branch::where('code', 'ICH-01')->first();

        $rows = [
            ['code' => 'EMP-001', 'name' => 'Rahul Das', 'role' => 'Waiter', 'phone' => '9800011001', 'email' => 'rahul.das@royalbengal.example', 'joining' => '2023-04-12', 'shift' => 'evening'],
            ['code' => 'EMP-002', 'name' => 'Ankit Roy', 'role' => 'Waiter', 'phone' => '9800011002', 'email' => null, 'joining' => '2023-06-01', 'shift' => 'evening'],
            ['code' => 'EMP-003', 'name' => 'Suman Ghosh', 'role' => 'Waiter', 'phone' => '9800011003', 'email' => null, 'joining' => '2024-01-20', 'shift' => 'morning'],
            ['code' => 'EMP-004', 'name' => 'Priya Sen', 'role' => 'Waiter', 'phone' => '9800011004', 'email' => null, 'joining' => '2024-03-10', 'shift' => 'morning'],
            ['code' => 'EMP-005', 'name' => 'Amit Sharma', 'role' => 'Cashier', 'phone' => '9800011005', 'email' => 'amit.sharma@royalbengal.example', 'joining' => '2022-11-05', 'shift' => 'fullday'],
            ['code' => 'EMP-006', 'name' => 'Rakesh Singh', 'role' => 'Restaurant Manager', 'phone' => '9800011006', 'email' => 'rakesh.singh@royalbengal.example', 'joining' => '2021-07-15', 'shift' => 'fullday'],
            ['code' => 'EMP-007', 'name' => 'Arjun Das', 'role' => 'Kitchen Manager', 'phone' => '9800011007', 'email' => null, 'joining' => '2022-02-18', 'shift' => 'fullday'],
            ['code' => 'EMP-008', 'name' => 'Chef Imran', 'role' => 'Chef', 'phone' => '9800011008', 'email' => null, 'joining' => '2021-09-01', 'shift' => 'fullday'],
            ['code' => 'EMP-009', 'name' => 'Sourav Roy', 'role' => 'Inventory Manager', 'phone' => '9800011009', 'email' => null, 'joining' => '2023-01-10', 'shift' => 'morning'],
            ['code' => 'EMP-010', 'name' => 'Nabila Khan', 'role' => 'Waiter', 'phone' => '9800011010', 'email' => null, 'joining' => '2025-02-01', 'shift' => 'evening', 'status' => 'inactive'],
            // Owner — referenced by the sidebar/dashboard avatar ("Aisha Rahman") but not in the Employees demo list.
            ['code' => 'EMP-000', 'name' => 'Aisha Rahman', 'role' => 'Restaurant Owner', 'phone' => '9800011000', 'email' => 'aisha.rahman@royalbengal.example', 'joining' => '2020-01-01', 'shift' => 'fullday'],
        ];

        foreach ($rows as $row) {
            $role = Role::where('name', $row['role'])->first();

            $user = null;
            if ($row['email']) {
                $user = User::firstOrCreate(
                    ['email' => $row['email']],
                    ['name' => $row['name'], 'password' => Hash::make('password')]
                );
            }

            Employee::firstOrCreate(
                ['employee_code' => $row['code']],
                [
                    'user_id' => $user?->id,
                    'role_id' => $role?->id,
                    'branch_id' => $branch?->id,
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'joining_date' => $row['joining'],
                    'shift' => $row['shift'],
                    'status' => $row['status'] ?? 'active',
                    'pos_pin_hash' => Hash::make('1234'),
                ]
            );
        }
    }
}
