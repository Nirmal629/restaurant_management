<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Floor;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Services\RealtimeNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        if ($data['status'] === 'available' && $table->orders()->whereIn('status', ['open', 'billing'])->exists()) {
            return response()->json(['message' => 'Close the active order before marking this table available.'], 422);
        }

        $table->update([
            'status' => $data['status'],
            'merged_with_table_id' => null,
            'is_merge_primary' => false,
        ]);
        $realtime->touch(['tables', 'reservations', 'orders', 'billing']);

        return response()->json([...$this->payload(), 'message' => 'Table status updated']);
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
            'reservations' => Reservation::with(['table', 'floor'])->whereDate('date', '>=', now()->subDay()->toDateString())->latest('date')->limit(80)->get()->map(fn (Reservation $reservation) => $this->reservationResource($reservation))->values()->all(),
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

        return [
            'dbId' => $table->id,
            'id' => $table->code,
            'name' => $table->name,
            'floor' => str($table->floor?->name ?? 'Floor')->slug()->toString(),
            'seats' => (int) $table->seats,
            'shape' => $table->shape,
            'status' => $table->status,
            'reservationId' => $reservation?->code,
            'reservationTime' => $reservation ? substr((string) $reservation->time, 0, 5) : null,
            'reservationCustomer' => $reservation?->customer_name,
            'reservationGuests' => $reservation ? (int) $reservation->guests : null,
            'guests' => $order?->guests,
            'waiter' => $order?->waiter?->name,
            'customer' => $order?->customer?->name ?? ($order ? 'Walk-in' : null),
            'orderCode' => $order?->code,
            'amount' => $order?->invoice?->grandTotal() ?? $order?->subtotal(),
            'since' => $order?->started_at ? (int) $order->started_at->diffInMinutes(now()) : null,
            'items' => $order?->items?->map(fn ($item) => ['name' => $item->menuItem?->name ?? 'Item', 'qty' => $item->qty])->values()->all() ?? [],
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
