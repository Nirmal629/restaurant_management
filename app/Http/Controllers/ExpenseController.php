<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    private const CATEGORIES = ['Rent', 'Electricity', 'Gas', 'Cleaning', 'Repair', 'Marketing', 'Packaging', 'Transport', 'Internet', 'Maintenance', 'Salary', 'Miscellaneous'];
    private const METHODS = ['Cash', 'UPI', 'Card', 'Bank Transfer', 'Other'];
    private const THRESHOLD = 10000;

    public function index()
    {
        return view('expenses', ['expenseModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $expense = Expense::create($this->attributes($data) + [
            'code' => $this->nextCode(),
            'employee_id' => auth()->user()?->employee?->id,
            'branch_id' => Branch::query()->first()?->id,
            'status' => $data['amount'] > self::THRESHOLD ? 'draft' : 'paid',
        ]);
        $expense->logActivity('Expense created');
        if ($expense->status === 'draft') $expense->logActivity('Submitted for approval - exceeds threshold');

        return response()->json(['expense' => $this->resource($expense->fresh(['employee', 'branch', 'activities'])), 'timeline' => $this->timeline(), 'message' => "{$expense->code} recorded"], 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $expense->update($this->attributes($this->validated($request)));
        $expense->logActivity('Expense updated');

        return response()->json(['expense' => $this->resource($expense->fresh(['employee', 'branch', 'activities'])), 'timeline' => $this->timeline(), 'message' => "{$expense->code} updated"]);
    }

    public function status(Request $request, Expense $expense): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'paid', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $expense->update(['status' => $data['status'], 'reject_reason' => $data['status'] === 'rejected' ? ($data['reason'] ?? null) : null]);
        $expense->logActivity(match ($data['status']) {
            'approved' => 'Approved',
            'paid' => 'Marked as paid',
            default => 'Rejected - ' . ($data['reason'] ?? ''),
        });

        return response()->json(['expense' => $this->resource($expense->fresh(['employee', 'branch', 'activities'])), 'timeline' => $this->timeline(), 'message' => "{$expense->code} {$data['status']}"]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $code = $expense->code;
        $expense->delete();
        return response()->json(['expenses' => $this->expenses(), 'timeline' => $this->timeline(), 'message' => "{$code} deleted"]);
    }

    public function export()
    {
        $rows = collect([['Code', 'Date', 'Category', 'Description', 'Vendor', 'Method', 'Amount', 'Employee', 'Status']])
            ->concat(Expense::with('employee')->orderBy('date')->get()->map(fn ($e) => [$e->code, $e->date?->toDateString(), $e->category, $e->description, $e->vendor, $e->payment_method, $e->amount, $e->employee?->name, $e->status]));
        $csv = $rows->map(fn ($row) => collect($row)->map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"')->implode(','))->implode("\n");
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="expenses.csv"']);
    }

    private function payload(): array
    {
        return ['venue' => ['name' => config('app.name'), 'branch' => Branch::query()->first()?->name ?? 'Main Branch'], 'operator' => ['name' => auth()->user()?->employee?->name ?? auth()->user()?->name ?? 'System'], 'categories' => self::CATEGORIES, 'methods' => self::METHODS, 'threshold' => self::THRESHOLD, 'expenses' => $this->expenses(), 'timeline' => $this->timeline()];
    }

    private function expenses(): array
    {
        return Expense::with(['employee', 'branch'])->latest('date')->latest('id')->get()->map(fn ($e) => $this->resource($e))->values()->all();
    }

    private function resource(Expense $e): array
    {
        return ['id' => $e->code, 'dbId' => $e->id, 'date' => $e->date?->format('d/m/Y'), 'category' => $e->category, 'description' => $e->description, 'vendor' => $e->vendor, 'method' => $e->payment_method, 'amount' => (float) $e->amount, 'employee' => $e->employee?->name ?? 'System', 'branch' => $e->branch?->name, 'status' => $e->status, 'receipt' => $e->receipt_attached, 'reference' => $e->reference, 'rejectReason' => $e->reject_reason, 'notes' => $e->notes];
    }

    private function timeline(): array
    {
        return Expense::with('activities')->get()->mapWithKeys(fn ($e) => [$e->code => $e->activities->map(fn ($a) => ['at' => $a->recorded_at?->format('d/m/Y H:i'), 'text' => $a->text])->values()->all()])->all();
    }

    private function validated(Request $request): array
    {
        return $request->validate(['date' => ['required'], 'category' => ['required', 'string'], 'amount' => ['required', 'numeric', 'min:0.01'], 'method' => ['required', 'string'], 'vendor' => ['nullable', 'string', 'max:255'], 'description' => ['required', 'string'], 'reference' => ['nullable', 'string'], 'notes' => ['nullable', 'string'], 'receipt' => ['boolean']]);
    }

    private function attributes(array $data): array
    {
        return ['date' => $this->parseDate($data['date']), 'category' => $data['category'], 'amount' => $data['amount'], 'payment_method' => $data['method'], 'vendor' => $data['vendor'] ?? null, 'description' => $data['description'], 'reference' => $data['reference'] ?? null, 'notes' => $data['notes'] ?? null, 'receipt_attached' => $data['receipt'] ?? false];
    }

    private function parseDate(string $date): string
    {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) return \Carbon\Carbon::createFromFormat('d/m/Y', $date)->toDateString();
        return \Carbon\Carbon::parse($date)->toDateString();
    }

    private function nextCode(): string
    {
        $prefix = 'EXP-' . now()->format('Y') . '-';
        $last = Expense::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(fn ($code) => (int) str_replace($prefix, '', $code))
            ->max() ?? 0;

        return $prefix . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }
}
