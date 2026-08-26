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
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservationController extends Controller
{
    private const RESERVATION_SLOT_MINUTES = 120;
    private const RESERVATION_HOLD_BEFORE_MINUTES = 30;

    public function index(): View
    {
        return view('reservations', ['reservationsPayload' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function store(Request $request, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $this->validated($request);
        $reservation = Reservation::create($this->reservationData($data, $request));
        $reservation->logActivity('Reservation created via ' . $reservation->source);
        $this->syncTableStatus($reservation);
        $realtime->touch(['reservations', 'tables']);

        return response()->json([...$this->payload(), 'reservation' => $this->resource($reservation->fresh(['table', 'floor', 'activities'])), 'message' => 'Reservation created'], 201);
    }

    public function update(Request $request, Reservation $reservation, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $this->validated($request, $reservation);
        $oldTableId = $reservation->table_id;

        $reservation->update($this->reservationData($data, $request, false));
        $reservation->logActivity('Reservation details updated');

        if ($oldTableId && $oldTableId !== $reservation->table_id) {
            $this->releaseTable($oldTableId);
        }
        $this->syncTableStatus($reservation);
        $realtime->touch(['reservations', 'tables']);

        return response()->json([...$this->payload(), 'reservation' => $this->resource($reservation->fresh(['table', 'floor', 'activities'])), 'message' => 'Reservation updated']);
    }

    public function status(Request $request, Reservation $reservation, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'arrived', 'cancelled', 'no_show'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reservation->update(['status' => $data['status']]);
        $reservation->logActivity(match ($data['status']) {
            'confirmed' => 'Marked Confirmed',
            'arrived' => 'Marked Arrived',
            'cancelled' => 'Cancelled - ' . ($data['reason'] ?? 'No reason'),
            'no_show' => 'Marked No Show',
        });

        if (in_array($data['status'], ['cancelled', 'no_show'], true) && $reservation->table_id) {
            $this->releaseTable($reservation->table_id);
        } else {
            $this->syncTableStatus($reservation);
        }

        $realtime->touch(['reservations', 'tables']);

        return response()->json([...$this->payload(), 'message' => 'Reservation updated']);
    }

    public function seat(Request $request, Reservation $reservation, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'table' => ['required', 'string'],
        ]);

        $table = RestaurantTable::where('code', $data['table'])->firstOrFail();
        if (! in_array($table->status, ['available', 'reserved'], true)) {
            return response()->json(['message' => 'Selected table is not available for seating.'], 422);
        }

        $customer = Customer::firstOrCreate(
            ['phone' => $reservation->phone],
            ['name' => $reservation->customer_name, 'email' => $reservation->email, 'joined_date' => now()->toDateString()]
        );

        $reservation->update([
            'table_id' => $table->id,
            'floor_id' => $table->floor_id,
            'customer_id' => $customer->id,
            'status' => 'seated',
        ]);
        $reservation->logActivity('Seated at ' . $table->code);

        $order = Order::create([
            'code' => $this->nextOrderCode(),
            'type' => 'dinein',
            'table_id' => $table->id,
            'customer_id' => $customer->id,
            'waiter_id' => $request->user()?->employee?->id,
            'guests' => $reservation->guests,
            'status' => 'open',
            'started_at' => now(),
        ]);

        $table->update(['status' => 'occupied']);
        $realtime->touch(['reservations', 'tables', 'orders', 'pos']);

        return response()->json([
            ...$this->payload(),
            'redirect' => route('pos', ['order' => $order->id]),
            'message' => 'Guest seated and order started',
        ]);
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
            'tables' => RestaurantTable::with('floor')->orderBy('code')->get()->map(fn (RestaurantTable $table) => [
                'dbId' => $table->id,
                'id' => $table->code,
                'floor' => str($table->floor?->name ?? 'Floor')->slug()->toString(),
                'seats' => (int) $table->seats,
                'status' => $table->status,
                'groupId' => $table->is_merge_primary ? 'merge-' . $table->id : ($table->merged_with_table_id ? 'merge-' . $table->merged_with_table_id : null),
                'mergedWithTableId' => $table->merged_with_table_id,
                'groupPrimary' => $table->is_merge_primary,
            ])->values()->all(),
            'reservations' => Reservation::with(['table', 'floor', 'activities'])->latest('date')->latest('time')->limit(200)->get()->map(fn (Reservation $reservation) => $this->resource($reservation))->values()->all(),
        ];
    }

    private function resource(Reservation $reservation): array
    {
        return [
            'id' => $reservation->code,
            'dbId' => $reservation->id,
            'customer' => $reservation->customer_name,
            'phone' => $reservation->phone,
            'email' => $reservation->email,
            'date' => $reservation->date?->toDateString(),
            'time' => substr((string) $reservation->time, 0, 5),
            'guests' => (int) $reservation->guests,
            'floor' => str($reservation->floor?->name ?? $reservation->table?->floor?->name ?? 'Floor')->slug()->toString(),
            'table' => $reservation->table?->code,
            'status' => $reservation->status,
            'occasion' => $reservation->occasion,
            'request' => $reservation->special_request,
            'source' => $reservation->source,
            'deposit' => (float) $reservation->deposit,
            'createdBy' => $reservation->creator?->name,
            'history' => $reservation->activities->map(fn ($activity) => [
                'at' => $activity->recorded_at?->diffForHumans() ?? '',
                'text' => $activity->text,
            ])->values()->all(),
        ];
    }

    private function validated(Request $request, ?Reservation $reservation = null): array
    {
        return $request->validate([
            'customer' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'guests' => ['required', 'integer', 'min:1'],
            'floor' => ['nullable', 'string'],
            'table' => ['nullable', 'string'],
            'occasion' => ['nullable', 'string', 'max:255'],
            'request' => ['nullable', 'string', 'max:500'],
            'source' => ['required', 'string', 'max:255'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function reservationData(array $data, Request $request, bool $creating = true): array
    {
        $table = ! empty($data['table']) ? RestaurantTable::where('code', $data['table'])->first() : null;
        if ($table && ! $this->tableAvailableForSlot($table, $data['date'], $data['time'], $data['guests'], $creating ? null : $request->route('reservation')?->id)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'table' => "{$table->code} is not available for the selected date and time.",
            ]);
        }

        $payload = [
            ...($creating ? ['code' => $this->nextReservationCode(), 'created_by' => $request->user()?->employee?->id, 'status' => 'pending'] : []),
            'customer_name' => $data['customer'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'date' => $data['date'],
            'time' => $data['time'],
            'guests' => $data['guests'],
            'floor_id' => $table?->floor_id ?? (! empty($data['floor']) ? $this->floorId($data['floor']) : null),
            'table_id' => $table?->id,
            'occasion' => $data['occasion'] ?? 'None',
            'special_request' => $data['request'] ?? null,
            'source' => $data['source'],
            'deposit' => $data['deposit'] ?? 0,
        ];

        return $payload;
    }

    private function syncTableStatus(Reservation $reservation): void
    {
        if (! $reservation->table_id || ! in_array($reservation->status, ['pending', 'confirmed', 'arrived'], true)) {
            return;
        }

        if ($this->reservationIsLive($reservation) && in_array($reservation->table?->status, ['available', 'reserved'], true)) {
            $reservation->table?->update(['status' => 'reserved']);
        }
    }

    private function releaseTable(int $tableId): void
    {
        $table = RestaurantTable::find($tableId);
        if ($table && $table->status === 'reserved') {
            $table->update(['status' => 'available']);
        }
    }

    private function tableAvailableForSlot(RestaurantTable $table, string $date, string $time, int $guests, ?int $ignoreReservationId = null): bool
    {
        $members = $this->tableGroupMembers($table);
        $capacity = $members->sum(fn (RestaurantTable $member) => (int) $member->seats);

        if ($capacity < $guests || $members->contains(fn (RestaurantTable $member) => $member->status === 'disabled')) {
            return false;
        }

        $slot = Carbon::parse("{$date} {$time}");
        $now = now();
        if ($date === $now->toDateString() && $slot->lessThanOrEqualTo($now->copy()->addMinutes(self::RESERVATION_SLOT_MINUTES))) {
            if ($members->contains(fn (RestaurantTable $member) => in_array($member->status, ['occupied', 'billing', 'cleaning'], true))) {
                return false;
            }
        }

        return ! Reservation::whereIn('table_id', $members->pluck('id'))
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'arrived', 'seated'])
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->get()
            ->contains(fn (Reservation $reservation) => abs(Carbon::parse($reservation->date->toDateString() . ' ' . substr((string) $reservation->time, 0, 5))->diffInMinutes($slot, false)) < self::RESERVATION_SLOT_MINUTES);
    }

    private function tableGroupMembers(RestaurantTable $table)
    {
        $groupId = $table->is_merge_primary ? $table->id : $table->merged_with_table_id;
        if (! $groupId) {
            return collect([$table]);
        }

        return RestaurantTable::whereKey($groupId)
            ->orWhere('merged_with_table_id', $groupId)
            ->get();
    }

    private function reservationIsLive(Reservation $reservation): bool
    {
        if (! $reservation->date || ! $reservation->time) {
            return false;
        }

        $slot = Carbon::parse($reservation->date->toDateString() . ' ' . substr((string) $reservation->time, 0, 5));

        return now()->between(
            $slot->copy()->subMinutes(self::RESERVATION_HOLD_BEFORE_MINUTES),
            $slot->copy()->addMinutes(self::RESERVATION_SLOT_MINUTES)
        );
    }

    private function floorId(string $key): ?int
    {
        return Floor::get()->first(fn (Floor $floor) => str($floor->name)->slug()->toString() === $key)?->id;
    }

    private function nextReservationCode(): string
    {
        $last = Reservation::where('code', 'like', 'RES-%')->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 300;

        return 'RES-' . $next;
    }

    private function nextOrderCode(): string
    {
        $year = now()->format('Y');
        $last = Order::where('code', 'like', "ORD-{$year}-%")->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 1;

        return 'ORD-' . $year . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
