<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Kot;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employees', ['employeeModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $password = $data['password'] ?? 'password';
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => $this->nextEmployeeCode(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'role_id' => Role::where('name', $data['role'])->value('id'),
            'branch_id' => Branch::query()->first()?->id,
            'joining_date' => $data['joiningDate'] ?? now()->toDateString(),
            'shift' => $data['shift'],
            'status' => 'active',
            'permission_overrides' => [],
        ]);

        return response()->json([
            'employee' => $this->employeeResource($employee->fresh(['role.permissions', 'branch', 'user'])),
            'message' => "{$employee->name} added. Temporary password: {$password}",
        ], 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $this->validated($request, $employee);
        $user = $this->syncLoginUser($employee, $data);

        $employee->update([
            'user_id' => $user->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'role_id' => Role::where('name', $data['role'])->value('id'),
            'joining_date' => $data['joiningDate'] ?? null,
            'shift' => $data['shift'],
        ]);

        return response()->json([
            'employee' => $this->employeeResource($employee->fresh(['role.permissions', 'branch', 'user'])),
            'message' => "{$employee->name} updated",
        ]);
    }

    public function status(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        $employee->update(['status' => $data['status']]);

        return response()->json([
            'employee' => $this->employeeResource($employee->fresh(['role.permissions', 'branch', 'user'])),
            'message' => "{$employee->name} marked " . ucfirst($data['status']),
        ]);
    }

    public function shift(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'shift' => ['required', Rule::in(['morning', 'evening', 'fullday'])],
        ]);

        $employee->update(['shift' => $data['shift']]);

        return response()->json([
            'employee' => $this->employeeResource($employee->fresh(['role.permissions', 'branch', 'user'])),
            'message' => "{$employee->name}'s shift updated",
        ]);
    }

    public function permissions(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'permissionOverrides' => ['present', 'array'],
            'permissionOverrides.*' => ['array'],
            'permissionOverrides.*.*' => ['string', 'exists:permissions,action'],
        ]);

        $employee->update(['permission_overrides' => $data['permissionOverrides']]);

        return response()->json([
            'employee' => $this->employeeResource($employee->fresh(['role.permissions', 'branch', 'user'])),
            'message' => 'Permissions updated',
        ]);
    }

    private function payload(): array
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $employees = Employee::with(['role.permissions', 'branch', 'user'])
            ->latest('id')
            ->get();

        $permissions = $roles->flatMap->permissions
            ->unique(fn ($permission) => $permission->module . ':' . $permission->action)
            ->sortBy([['module', 'asc'], ['action', 'asc']])
            ->values();

        return [
            'venue' => [
                'name' => config('app.name', 'Restaurant'),
                'branch' => Branch::query()->first()?->name ?? 'Main Branch',
            ],
            'roles' => $roles->pluck('name')->values()->all(),
            'modules' => $permissions->pluck('module')->unique()->values()->all(),
            'actions' => $permissions->pluck('action')->unique()->values()->all(),
            'roleDefaults' => $roles->mapWithKeys(fn ($role) => [
                $role->name => $role->permissions
                    ->groupBy('module')
                    ->map(fn ($items) => $items->pluck('action')->values()->all())
                    ->all(),
            ])->all(),
            'shiftTypes' => [
                'morning' => ['label' => 'Morning', 'start' => '09:00', 'end' => '16:00'],
                'evening' => ['label' => 'Evening', 'start' => '16:00', 'end' => '23:00'],
                'fullday' => ['label' => 'Full Day', 'start' => '09:00', 'end' => '23:00'],
            ],
            'employees' => $employees->map(fn ($employee) => $this->employeeResource($employee))->values()->all(),
        ];
    }

    private function employeeResource(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employeeId' => $employee->employee_code,
            'name' => $employee->name,
            'role' => $employee->role?->name ?? 'Staff',
            'phone' => $employee->phone,
            'email' => $employee->email,
            'address' => $employee->address,
            'joiningDate' => $employee->joining_date?->toDateString(),
            'shift' => $employee->shift,
            'status' => $employee->status,
            'hasLogin' => filled($employee->user_id),
            'loginEmail' => $employee->user?->email ?? $employee->email,
            'pinSet' => filled($employee->pos_pin_hash),
            'activeTables' => $this->activeTables($employee),
            'permissionOverrides' => $employee->permission_overrides ?? [],
            'activity' => $this->activity($employee),
            'performance' => $this->performance($employee),
        ];
    }

    private function activeTables(Employee $employee): int
    {
        return Order::where('waiter_id', $employee->id)
            ->whereIn('status', ['open', 'billing'])
            ->whereNotNull('table_id')
            ->distinct('table_id')
            ->count('table_id');
    }

    private function activity(Employee $employee): array
    {
        $orders = Order::where('waiter_id', $employee->id)
            ->latest('started_at')
            ->limit(3)
            ->get()
            ->map(fn ($order) => [
                'at' => $order->started_at?->format('d/m/Y H:i') ?? $order->created_at?->format('d/m/Y H:i'),
                'text' => "Order created - {$order->code}",
            ]);

        $approvals = Invoice::where('bill_discount_approved_by', $employee->id)
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(fn ($invoice) => [
                'at' => $invoice->updated_at?->format('d/m/Y H:i'),
                'text' => "Approved discount - {$invoice->code}",
            ]);

        return $orders->concat($approvals)->sortByDesc('at')->take(5)->values()->all();
    }

    private function performance(Employee $employee): ?array
    {
        if ($employee->role?->name === 'Waiter') {
            $orders = Order::with('invoice')
                ->where('waiter_id', $employee->id)
                ->whereDate('started_at', '>=', now()->subDays(30)->toDateString())
                ->get();

            $sales = $orders->sum(fn ($order) => $order->invoice?->grandTotal() ?? 0);
            $count = $orders->count();

            return [
                'orders' => $count,
                'sales' => (int) $sales,
                'avgBill' => $count ? (int) round($sales / $count) : 0,
                'tablesServed' => $orders->whereNotNull('table_id')->pluck('table_id')->unique()->count(),
            ];
        }

        if (in_array($employee->role?->name, ['Chef', 'Kitchen Manager'], true)) {
            $items = Kot::whereDate('sent_at', '>=', now()->subDays(30)->toDateString())
                ->with('items')
                ->get()
                ->flatMap->items
                ->filter(fn ($item) => $item->status !== 'cancelled');

            $prepMinutes = $items
                ->filter(fn ($item) => $item->sent_at && $item->ready_at)
                ->map(fn ($item) => $item->sent_at->diffInMinutes($item->ready_at));

            return [
                'ordersPrepared' => $items->count(),
                'avgPrepTime' => $prepMinutes->count() ? (int) round($prepMinutes->avg()) : 0,
            ];
        }

        return null;
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('employees', 'phone')->ignore($employee)],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee),
                Rule::unique('users', 'email')->ignore($employee?->user_id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'shift' => ['required', Rule::in(['morning', 'evening', 'fullday'])],
            'joiningDate' => ['nullable', 'date'],
            'password' => [$employee ? 'sometimes' : 'nullable', 'nullable', 'string', 'min:8'],
        ]);
    }

    private function syncLoginUser(Employee $employee, array $data): User
    {
        $user = $employee->user;

        if (! $user) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password'),
            ]);
        }

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (filled($data['password'] ?? null)) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        return $user;
    }

    private function nextEmployeeCode(): string
    {
        $last = Employee::where('employee_code', 'like', 'EMP-%')
            ->get()
            ->map(fn ($employee) => (int) str_replace('EMP-', '', $employee->employee_code))
            ->max() ?? 0;

        return 'EMP-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
