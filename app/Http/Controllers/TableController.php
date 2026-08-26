<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Floor;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Services\RealtimeNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Carbon\Carbon;

class TableController extends Controller
{
    public function index(): View
    {
        return view('tables', ['tablesPayload' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function start(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'guests' => ['required', 'integer', 'min:1'],
            'customer' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! in_array($table->status, ['available', 'reserved'], true)) {
            return response()->json(['message' => 'Only available or reserved tables can start a new order.'], 422);
        }

        $customerId = null;
        if (! empty($data['customer']) && ! empty($data['phone'])) {
            $customerId = Customer::firstOrCreate(
                ['phone' => $data['phone']],
                ['name' => $data['customer'], 'joined_date' => now()->toDateString()]
            )->id;
        }

        $order = Order::create([
            'code' => $this->nextOrderCode(),
            'type' => 'dinein',
            'table_id' => $table->id,
            'customer_id' => $customerId,
            'waiter_id' => $request->user()?->employee?->id,
            'guests' => $data['guests'],
            'status' => 'open',
            'started_at' => now(),
        ]);

        $table->update(['status' => 'occupied']);

        Reservation::where('table_id', $table->id)
            ->whereIn('status', ['confirmed', 'arrived'])
            ->whereDate('date', now()->toDateString())
            ->update(['status' => 'seated']);

        $realtime->touch(['tables', 'reservations', 'orders', 'pos']);

        return response()->json([
            ...$this->payload(),
            'redirect' => route('pos', ['order' => $order->id]),
            'message' => 'Table order started',
        ], 201);
    }

    public function reserve(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'customer' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date' => ['nullable', 'date'],
            'time' => ['required', 'string', 'max:20'],
            'guests' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($table->status !== 'available') {
            return response()->json(['message' => 'Only available tables can be reserved.'], 422);
        }

        $reservation = Reservation::create([
            'code' => $this->nextReservationCode(),
            'customer_name' => $data['customer'],
            'phone' => $data['phone'],
            'date' => $data['date'] ?? now()->toDateString(),
            'time' => $this->normalizeTime($data['time']),
            'guests' => $data['guests'],
            'floor_id' => $table->floor_id,
            'table_id' => $table->id,
            'status' => 'confirmed',
            'special_request' => $data['notes'] ?? null,
            'source' => 'Walk-in',
            'created_by' => $request->user()?->employee?->id,
        ]);
        $reservation->logActivity('Reservation created from Tables');

        $table->update(['status' => 'reserved']);
        $realtime->touch(['tables', 'reservations']);

        return response()->json([...$this->payload(), 'message' => 'Reservation created'], 201);
    }

    public function status(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'cleaning', 'disabled'])],
        ]);

        if ($data['status'] === 'available') {
            $activeOrders = $table->orders()->with('items')->whereIn('status', ['open', 'billing'])->get();

            if ($table->status !== 'cleaning') {
                return response()->json(['message' => 'Close the active order before marking this table available.'], 422);
            }

            if ($activeOrders->isNotEmpty()) {
                $activeOrders->each->update(['status' => 'cancelled']);
            }
        }

        $table->update([
            'status' => $data['status'],
            'merged_with_table_id' => null,
            'is_merge_primary' => false,
        ]);
        $realtime->touch(['tables', 'reservations', 'orders', 'billing']);

        return response()->json([...$this->payload(), 'message' => 'Table status updated']);
    }

    public function transfer(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'to' => ['required', 'string', 'exists:restaurant_tables,code'],
        ]);

        $target = RestaurantTable::where('code', $data['to'])->firstOrFail();

        if ($target->is($table)) {
            return response()->json(['message' => 'Choose a different table.'], 422);
        }

        if ($target->status !== 'available') {
            return response()->json(['message' => 'Target table must be available.'], 422);
        }

        DB::transaction(function () use ($table, $target) {
            $table->refresh();
            $target->refresh();

            if ($table->status === 'reserved') {
                $reservation = $table->reservations()
                    ->whereIn('status', ['pending', 'confirmed', 'arrived'])
                    ->whereDate('date', now()->toDateString())
                    ->orderBy('time')
                    ->first();

                if (! $reservation) {
                    abort(422, 'No active reservation found for this table.');
                }

                if ($target->seats < $reservation->guests) {
                    abort(422, 'Target table does not have enough seats.');
                }

                $reservation->update([
                    'table_id' => $target->id,
                    'floor_id' => $target->floor_id,
                ]);
                $table->update(['status' => 'available']);
                $target->update(['status' => 'reserved']);

                return;
            }

            if (! in_array($table->status, ['occupied', 'billing'], true)) {
                abort(422, 'Only occupied, billing, or reserved tables can be transferred.');
            }

            $order = $table->orders()
                ->whereIn('status', ['open', 'billing'])
                ->latest('id')
                ->first();

            if (! $order) {
                abort(422, 'No active order found for this table.');
            }

            if ($target->seats < (int) ($order->guests ?? 1)) {
                abort(422, 'Target table does not have enough seats.');
            }

            $targetStatus = $table->status;
            $table->orders()
                ->whereIn('status', ['open', 'billing'])
                ->update(['table_id' => $target->id]);
            $target->update(['status' => $targetStatus]);
            $table->update(['status' => 'cleaning']);
        });

        $realtime->touch(['tables', 'orders', 'pos', 'billing', 'reservations']);

        return response()->json([...$this->payload(), 'message' => "Table {$table->code} transferred to {$target->code}"]);
    }

    public function waiter(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'waiter' => ['required', 'string', 'exists:employees,name'],
        ]);

        $order = $table->orders()->whereIn('status', ['open', 'billing'])->latest('id')->first();
        if (! $order) {
            return response()->json(['message' => 'No active order found for this table.'], 422);
        }

        $employee = Employee::where('name', $data['waiter'])->firstOrFail();
        $order->update(['waiter_id' => $employee->id]);
        $realtime->touch(['tables', 'orders', 'pos', 'billing']);

        return response()->json([...$this->payload(), 'message' => "{$employee->name} assigned to {$table->code}"]);
    }

    public function merge(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'with' => ['required', 'string', 'exists:restaurant_tables,code'],
        ]);

        $secondary = RestaurantTable::where('code', $data['with'])->firstOrFail();

        if ($secondary->is($table)) {
            return response()->json(['message' => 'Choose a different table to merge.'], 422);
        }

        if (! in_array($table->status, ['occupied', 'billing'], true)) {
            return response()->json(['message' => 'Only occupied or billing tables can be merged.'], 422);
        }

        if ($secondary->status !== 'available') {
            return response()->json(['message' => 'Only available tables can be merged in.'], 422);
        }

        if ($secondary->floor_id !== $table->floor_id) {
            return response()->json(['message' => 'Only tables on the same floor can be merged.'], 422);
        }

        DB::transaction(function () use ($table, $secondary) {
            $table->update([
                'is_merge_primary' => true,
                'merged_with_table_id' => null,
            ]);

            $secondary->update([
                'status' => $table->status,
                'merged_with_table_id' => $table->id,
                'is_merge_primary' => false,
            ]);
        });

        $realtime->touch(['tables', 'orders', 'pos', 'billing']);

        return response()->json([
            ...$this->payload(),
            'merge' => [
                'primary' => $table->code,
                'secondary' => $secondary->code,
                'groupId' => 'merge-' . $table->id,
            ],
            'message' => "{$secondary->code} merged with {$table->code}",
        ]);
    }

    public function store(Request $request, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:restaurant_tables,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'floor' => ['required', 'string'],
            'seats' => ['required', 'integer', 'min:1'],
            'shape' => ['required', Rule::in(['square', 'rect', 'round'])],
        ]);

        RestaurantTable::create([
            'floor_id' => $this->floorId($data['floor']),
            'code' => $data['code'],
            'name' => $data['name'] ?? null,
            'seats' => $data['seats'],
            'shape' => $data['shape'],
            'status' => 'available',
        ]);
        $realtime->touch(['tables', 'reservations', 'pos']);

        return response()->json([...$this->payload(), 'message' => 'Table added'], 201);
    }

    public function update(Request $request, RestaurantTable $table, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'floor' => ['required', 'string'],
            'seats' => ['required', 'integer', 'min:1'],
            'shape' => ['required', Rule::in(['square', 'rect', 'round'])],
            'active' => ['required', 'boolean'],
        ]);

        $table->update([
            'name' => $data['name'] ?? null,
            'floor_id' => $this->floorId($data['floor']),
            'seats' => $data['seats'],
            'shape' => $data['shape'],
            'status' => $data['active'] ? ($table->status === 'disabled' ? 'available' : $table->status) : 'disabled',
        ]);
        $realtime->touch(['tables', 'reservations', 'pos']);

        return response()->json([...$this->payload(), 'message' => 'Table updated']);
    }

    public function storeFloor(Request $request, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['required', 'boolean'],
        ]);

        Floor::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'display_order' => $data['order'] ?? 0,
            'is_active' => $data['active'],
        ]);
        $realtime->touch(['tables', 'reservations', 'pos']);

        return response()->json([...$this->payload(), 'message' => 'Floor added'], 201);
    }

    private function payload(): array
    {
        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Main Branch'],
            'operator' => ['name' => request()->user()?->employee?->name ?? request()->user()?->name ?? 'Operator'],
            'floors' => Floor::where('is_active', true)->orderBy('display_order')->orderBy('name')->get()->map(fn (Floor $floor) => [
                'id' => $floor->id,
                'key' => str($floor->name)->slug()->toString(),
                'label' => $floor->name,
            ])->values()->all(),
            'tables' => RestaurantTable::with(['floor', 'orders.items', 'orders.invoice', 'reservations'])->orderBy('code')->get()->map(fn (RestaurantTable $table) => $this->tableResource($table))->values()->all(),
            'reservations' => Reservation::with(['table', 'floor'])
                ->whereBetween('date', [now()->toDateString(), now()->addDay()->toDateString()])
                ->whereIn('status', ['pending', 'confirmed', 'arrived', 'seated'])
                ->orderBy('date')
                ->orderBy('time')
                ->limit(160)
                ->get()
                ->map(fn (Reservation $reservation) => $this->reservationResource($reservation))
                ->values()
                ->all(),
        ];
    }

    private function tableResource(RestaurantTable $table): array
    {
        $order = $table->orders->whereIn('status', ['open', 'billing'])->sortByDesc('id')->first();
        $reservation = $table->reservations
            ->filter(fn (Reservation $reservation) => in_array(strtolower($reservation->status), ['pending', 'confirmed', 'arrived'], true))
            ->filter(fn (Reservation $reservation) => $reservation->date?->toDateString() === now()->toDateString())
            ->sortBy('time')
            ->first();
        $since = $order?->started_at ? (int) $order->started_at->diffInMinutes(now()) : null;
        if ($table->status === 'cleaning' && ! $order) {
            $since = $table->updated_at ? (int) $table->updated_at->diffInMinutes(now()) : 0;
        }

        return [
            'dbId' => $table->id,
            'id' => $table->code,
            'name' => $table->name,
            'floor' => str($table->floor?->name ?? 'Floor')->slug()->toString(),
            'seats' => (int) $table->seats,
            'shape' => $table->shape,
            'status' => $table->status,
            'groupId' => $table->is_merge_primary ? 'merge-' . $table->id : ($table->merged_with_table_id ? 'merge-' . $table->merged_with_table_id : null),
            'mergedWithTableId' => $table->merged_with_table_id,
            'groupPrimary' => $table->is_merge_primary,
            'reservationId' => $reservation?->code,
            'reservationDate' => $reservation?->date?->toDateString(),
            'reservationTime' => $reservation ? substr((string) $reservation->time, 0, 5) : null,
            'reservationCustomer' => $reservation?->customer_name,
            'reservationPhone' => $reservation?->phone,
            'reservationGuests' => $reservation ? (int) $reservation->guests : null,
            'reservationNotes' => $reservation?->special_request,
            'guests' => $order?->guests,
            'waiter' => $order?->waiter?->name,
            'customer' => $order?->customer?->name ?? ($order ? 'Walk-in' : null),
            'orderId' => $order?->id,
            'orderCode' => $order?->code,
            'amount' => $order?->invoice?->grandTotal() ?? $order?->subtotal(),
            'since' => $since,
            'kitchen' => $order ? [
                'new' => $order->items->where('status', 'sent')->count(),
                'prep' => $order->items->whereIn('status', ['accepted', 'preparing'])->count(),
                'ready' => $order->items->where('status', 'ready')->count(),
            ] : null,
            'items' => $order?->items?->map(fn ($item) => [
                'name' => $item->menuItem?->name ?? 'Item',
                'qty' => $item->qty,
                'state' => strtoupper($item->status),
            ])->values()->all() ?? [],
        ];
    }

    private function reservationResource(Reservation $reservation): array
    {
        return [
            'id' => $reservation->code,
            'dbId' => $reservation->id,
            'tableId' => $reservation->table?->code,
            'customer' => $reservation->customer_name,
            'phone' => $reservation->phone,
            'date' => $reservation->date?->toDateString(),
            'time' => substr((string) $reservation->time, 0, 5),
            'guests' => (int) $reservation->guests,
            'notes' => $reservation->special_request,
            'status' => strtoupper($reservation->status),
        ];
    }

    private function floorId(string $key): int
    {
        return Floor::get()->first(fn (Floor $floor) => str($floor->name)->slug()->toString() === $key)?->id
            ?? Floor::firstOrCreate(['name' => str($key)->headline()->toString()])->id;
    }

    private function nextOrderCode(): string
    {
        $year = now()->format('Y');
        $last = Order::where('code', 'like', "ORD-{$year}-%")->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 1;

        return 'ORD-' . $year . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeTime(string $value): string
    {
        foreach (['H:i', 'G:i', 'g:i A', 'g:i a', 'h:i A', 'h:i a'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('H:i');
            } catch (\Throwable) {
                //
            }
        }

        abort(422, 'Enter reservation time like 20:00 or 8:00 PM.');
    }

    private function nextReservationCode(): string
    {
        $last = Reservation::where('code', 'like', 'RES-%')->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 300;

        return 'RES-' . $next;
    }
}
