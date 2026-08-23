<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /** Mirrors resources/js/expenses/demo-data.js EXPENSES exactly. */
    public function run(): void
    {
        $branch = Branch::where('code', 'ICH-01')->first();

        $rows = [
            ['code' => 'EXP-2026-0038', 'date' => '2026-08-23', 'cat' => 'Gas', 'desc' => 'LPG commercial cylinder refill ×4', 'vendor' => 'Ichapur Gas Agency', 'method' => 'Cash', 'amount' => 6400, 'emp' => 'EMP-005', 'status' => 'paid', 'receipt' => true],
            ['code' => 'EXP-2026-0037', 'date' => '2026-08-23', 'cat' => 'Cleaning', 'desc' => 'Weekly deep-cleaning service', 'vendor' => 'Sparkle Facility Services', 'method' => 'UPI', 'amount' => 3200, 'emp' => 'EMP-006', 'status' => 'paid', 'receipt' => true],
            ['code' => 'EXP-2026-0036', 'date' => '2026-08-22', 'cat' => 'Electricity', 'desc' => 'August electricity bill', 'vendor' => 'WBSEDCL', 'method' => 'Bank Transfer', 'amount' => 18450, 'emp' => 'EMP-006', 'status' => 'approved', 'receipt' => true],
            ['code' => 'EXP-2026-0035', 'date' => '2026-08-22', 'cat' => 'Repair', 'desc' => 'Walk-in freezer compressor repair', 'vendor' => 'CoolTech Services', 'method' => 'Cash', 'amount' => 4500, 'emp' => 'EMP-009', 'status' => 'paid', 'receipt' => false],
            ['code' => 'EXP-2026-0034', 'date' => '2026-08-21', 'cat' => 'Marketing', 'desc' => 'Instagram ad campaign — August', 'vendor' => 'Meta Ads', 'method' => 'Card', 'amount' => 5000, 'emp' => 'EMP-006', 'status' => 'paid', 'receipt' => true],
            ['code' => 'EXP-2026-0033', 'date' => '2026-08-20', 'cat' => 'Packaging', 'desc' => 'Takeaway containers — 500 units', 'vendor' => 'EcoPack Supplies', 'method' => 'UPI', 'amount' => 6200, 'emp' => 'EMP-005', 'status' => 'paid', 'receipt' => true],
            ['code' => 'EXP-2026-0032', 'date' => '2026-08-19', 'cat' => 'Transport', 'desc' => 'Vegetable delivery fuel reimbursement', 'vendor' => null, 'method' => 'Cash', 'amount' => 800, 'emp' => 'EMP-009', 'status' => 'paid', 'receipt' => false],
            ['code' => 'EXP-2026-0031', 'date' => '2026-08-18', 'cat' => 'Internet', 'desc' => 'Broadband + POS network — August', 'vendor' => 'Airtel Business', 'method' => 'Bank Transfer', 'amount' => 2499, 'emp' => 'EMP-006', 'status' => 'paid', 'receipt' => true],
            ['code' => 'EXP-2026-0030', 'date' => '2026-08-17', 'cat' => 'Rent', 'desc' => 'Shop rent — August', 'vendor' => 'Property Owner', 'method' => 'Bank Transfer', 'amount' => 65000, 'emp' => 'EMP-006', 'status' => 'approved', 'receipt' => false],
            ['code' => 'EXP-2026-0029', 'date' => '2026-08-16', 'cat' => 'Maintenance', 'desc' => 'AC servicing — 3 units', 'vendor' => 'CoolTech Services', 'method' => 'Cash', 'amount' => 3600, 'emp' => 'EMP-009', 'status' => 'draft', 'receipt' => false],
            ['code' => 'EXP-2026-0028', 'date' => '2026-08-15', 'cat' => 'Miscellaneous', 'desc' => 'Independence Day decoration', 'vendor' => 'Local vendor', 'method' => 'Cash', 'amount' => 1200, 'emp' => 'EMP-005', 'status' => 'rejected', 'receipt' => false, 'reject' => 'No prior approval taken'],
            ['code' => 'EXP-2026-0027', 'date' => '2026-08-10', 'cat' => 'Salary', 'desc' => 'Staff advance — kitchen team', 'vendor' => null, 'method' => 'Cash', 'amount' => 15000, 'emp' => 'EMP-006', 'status' => 'approved', 'receipt' => false],
        ];

        foreach ($rows as $row) {
            $expense = Expense::firstOrCreate(
                ['code' => $row['code']],
                [
                    'date' => $row['date'], 'category' => $row['cat'], 'description' => $row['desc'], 'vendor' => $row['vendor'],
                    'payment_method' => $row['method'], 'amount' => $row['amount'],
                    'employee_id' => Employee::where('employee_code', $row['emp'])->value('id'),
                    'branch_id' => $branch?->id, 'status' => $row['status'], 'receipt_attached' => $row['receipt'],
                    'reject_reason' => $row['reject'] ?? null,
                ]
            );

            if ($expense->wasRecentlyCreated) {
                $expense->logActivity('Expense created by ' . ($expense->employee?->name ?? 'system'));
                if ($row['status'] === 'rejected') {
                    $expense->logActivity('Rejected — ' . $row['reject']);
                } elseif (in_array($row['status'], ['approved', 'paid'], true) && $row['amount'] > 10000) {
                    $expense->logActivity('Approved by Aisha Rahman (Owner)');
                }
            }
        }
    }
}
