<?php

namespace Tests\Feature;

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_value_expense_is_created_as_draft_and_can_be_paid(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Expenses', 'Create'],
            ['Expenses', 'Approve'],
        ]);

        Expense::create([
            'code' => 'EXP-' . now()->format('Y') . '-0038',
            'date' => now()->toDateString(),
            'category' => 'Rent',
            'description' => 'Previous rent',
            'payment_method' => 'Cash',
            'amount' => 500,
            'status' => 'paid',
        ]);

        $create = $this->postJson('/expenses', [
            'date' => now()->toDateString(),
            'category' => 'Repair',
            'amount' => 12500,
            'method' => 'Bank Transfer',
            'vendor' => 'AC Services',
            'description' => 'Kitchen AC compressor repair',
            'reference' => 'BILL-55',
            'receipt' => true,
        ]);

        $create->assertCreated()
            ->assertJsonPath('expense.id', 'EXP-' . now()->format('Y') . '-0039')
            ->assertJsonPath('expense.status', 'draft');

        $expense = Expense::where('code', $create->json('expense.id'))->firstOrFail();

        $this->patchJson("/expenses/{$expense->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('expense.status', 'approved');

        $this->patchJson("/expenses/{$expense->id}/status", ['status' => 'paid'])
            ->assertOk()
            ->assertJsonPath('expense.status', 'paid');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'paid',
            'amount' => 12500,
        ]);
        $this->assertDatabaseHas('expense_activities', ['expense_id' => $expense->id, 'text' => 'Marked as paid']);
    }
}
