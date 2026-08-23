<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Kot;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\RealtimeNotifier;
use App\Services\StockConsumptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PosController extends Controller
{
    public function index()
    {
        return view('pos', ['posPayload' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function sendKot(Request $request, StockConsumptionService $stock, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['nullable', 'integer', 'exists:orders,id'],
            'orderType' => ['required', 'string', 'in:dinein,takeaway,delivery'],
            'table' => ['nullable', 'string'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'token' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menuItemId' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.variant' => ['nullable', 'string', 'max:255'],
            'items.*.modifiers' => ['array'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $menuItems = MenuItem::with('recipe.lines.ingredient')->whereIn('id', collect($data['items'])->pluck('menuItemId'))->get()->keyBy('id');

        foreach ($data['items'] as $line) {
            try {
                $stock->assertAvailable($menuItems[$line['menuItemId']], (int) $line['qty']);
            } catch (RuntimeException $e) {
                throw ValidationException::withMessages(['items' => $e->getMessage()]);
            }
        }

        $employee = $request->user()?->employee;

        $order = DB::transaction(function () use ($data, $menuItems, $stock, $employee) {
            $order = isset($data['orderId'])
                ? Order::lockForUpdate()->findOrFail($data['orderId'])
                : Order::create([
                    'code' => $this->nextOrderCode(),
                    'type' => $data['orderType'],
                    'table_id' => $this->tableId($data['table'] ?? null),
                    'customer_id' => $data['customerId'] ?? null,
                    'waiter_id' => $employee?->id,
                    'guests' => $data['guests'] ?? null,
                    'token' => $data['token'] ?? null,
                    'status' => 'open',
                    'started_at' => now(),
                ]);

            if ($order->status !== 'open') {
                throw ValidationException::withMessages(['order' => 'Only open orders can receive new KOT items.']);
            }

            $round = ((int) $order->kots()->max('round')) + 1;
            $kot = Kot::create([
                'code' => $this->nextKotCode(),
                'order_id' => $order->id,
                'round' => $round,
                'printer' => 'Kitchen',
                'sent_at' => now(),
            ]);

            foreach ($data['items'] as $line) {
                $menuItem = $menuItems[$line['menuItemId']];
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'variant_label' => $line['variant'] ?? null,
                    'qty' => $line['qty'],
                    'unit_price' => $menuItem->base_price,
                    'modifiers' => $line['modifiers'] ?? [],
                    'note' => $line['note'] ?? null,
                    'kitchen_station_id' => $menuItem->kitchen_station_id,
                    'kot_round' => $round,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $stock->consume($item, $employee);
            }

            $order->table?->update(['status' => 'occupied']);

            return $order->fresh($this->orderRelations());
        });

        $realtime->touch(['pos', 'orders', 'kitchen', 'tables', 'inventory', 'menu']);

        return response()->json([
            ...$this->payload($order),
            'message' => 'KOT sent',
        ], 201);
    }

    public function cancelItem(Request $request, OrderItem $item, StockConsumptionService $stock, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        if ($item->status === 'served') {
            return response()->json(['message' => 'Served items must be refunded from Billing.'], 422);
        }

        $stock->reverse($item, 'SALE_CANCEL_REVERSAL', $request->user()?->employee);
        $item->update(['status' => 'cancelled', 'cancel_reason' => $data['reason']]);
        $realtime->touch(['pos', 'orders', 'kitchen', 'tables', 'inventory', 'menu']);

        return response()->json([
            ...$this->payload($item->order->fresh($this->orderRelations())),
            'message' => 'Item cancelled',
        ]);
    }

    public function itemStatus(Request $request, OrderItem $item, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:served'],
        ]);

        if ($item->status !== 'ready') {
            return response()->json(['message' => 'Only ready kitchen items can be marked served from POS.'], 422);
        }

        $item->update(['status' => $data['status']]);
        $realtime->touch(['pos', 'orders', 'kitchen', 'tables', 'billing']);

        return response()->json([
            ...$this->payload($item->order->fresh($this->orderRelations())),
            'message' => 'Item marked served',
        ]);
    }

    public function sendToBilling(Order $order, RealtimeNotifier $realtime): JsonResponse
    {
        $order = $order->load($this->orderRelations());

        if ($order->status !== 'open' && $order->status !== 'billing') {
            return response()->json(['message' => 'Only active orders can be moved to billing.'], 422);
        }

        if ($order->items->isEmpty()) {
            return response()->json(['message' => 'Add and send at least one item before billing.'], 422);
        }

        if ($order->items->where('status', 'unsent')->isNotEmpty()) {
            return response()->json(['message' => 'Send all new items to KOT before billing.'], 422);
        }

        if ($order->items->whereNotIn('status', ['served', 'cancelled'])->isNotEmpty()) {
            return response()->json(['message' => 'Serve or cancel all kitchen items before billing.'], 422);
        }

        if ($order->status !== 'billing') {
            $order->update(['status' => 'billing']);
            $order->table?->update(['status' => 'billing']);
        }

        $realtime->touch(['pos', 'orders', 'billing', 'tables']);

        return response()->json([
            ...$this->payload($order->fresh($this->orderRelations())),
            'redirect' => route('billing', ['order' => $order->id]),
            'message' => 'Order is ready for billing',
        ]);
    }

    private function payload(?Order $activeOrder = null): array
    {
        $activeOrder ??= Order::with($this->orderRelations())->whereIn('status', ['open', 'billing'])->latest('id')->first();

        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Main Branch'],
            'operator' => $this->operator(),
            'categories' => $this->categories(),
            'menu' => $this->menu(),
            'customers' => Customer::orderBy('name')->limit(20)->get(['id', 'name', 'phone', 'loyalty_points'])->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'points' => $c->loyalty_points,
            ])->all(),
            'activeOrder' => $activeOrder ? $this->orderResource($activeOrder) : null,
            'runningOrders' => Order::with($this->orderRelations())->whereIn('status', ['open', 'billing'])->latest('id')->limit(20)->get()->map(fn ($o) => $this->orderResource($o))->all(),
            'readyAlerts' => $activeOrder ? $this->readyAlerts($activeOrder) : [],
        ];
    }

    private function menu(): array
    {
        return MenuItem::with(['category', 'station', 'recipe.lines.ingredient'])->orderBy('display_order')->orderBy('name')->get()->map(function (MenuItem $item) {
            $stock = $this->stockStatus($item);

            return [
                'id' => $item->id,
                'code' => $item->sku,
                'name' => $item->name,
                'price' => (float) $item->base_price,
                'cat' => str($item->category?->name ?? 'Other')->slug()->toString(),
                'diet' => $item->diet_type,
                'station' => $item->station?->name ?? 'Kitchen',
                'prep' => $item->prep_time_minutes,
                'stock' => $stock['status'],
                'left' => $stock['left'],
                'tracked' => $item->stock_tracked,
                'mods' => [],
            ];
        })->all();
    }

    private function categories(): array
    {
        $base = [
            ['key' => 'all', 'label' => 'All Menu', 'icon' => 'grid'],
            ['key' => 'favorites', 'label' => 'Favorites', 'icon' => 'star'],
            ['key' => 'recent', 'label' => 'Recent', 'icon' => 'clock'],
        ];

        return array_merge($base, MenuCategory::orderBy('display_order')->get()->map(fn ($category) => [
            'key' => str($category->name)->slug()->toString(),
            'label' => $category->name,
            'icon' => 'tag',
        ])->all());
    }

    private function orderResource(Order $order): array
    {
        return [
            'id' => $order->id,
            'code' => $order->code,
            'type' => $order->type,
            'table' => $order->table?->name,
            'guests' => $order->guests,
            'waiter' => $order->waiter?->name,
            'customer' => $order->customer ? ['id' => $order->customer->id, 'name' => $order->customer->name, 'phone' => $order->customer->phone] : null,
            'token' => $order->token,
            'items' => $order->items->map(fn (OrderItem $item) => [
                'uid' => $item->id,
                'id' => $item->id,
                'ref' => $item->menu_item_id,
                'name' => $item->menuItem?->name,
                'price' => (float) $item->unit_price,
                'qty' => (int) $item->qty,
                'station' => $item->station?->name ?? $item->menuItem?->station?->name ?? 'Kitchen',
                'variant' => $item->variant_label,
                'modifiers' => $item->modifiers ?? [],
                'note' => $item->note,
                'status' => $item->status,
                'kot' => $item->kot_round,
                'sentAt' => $item->sent_at?->format('H:i'),
                'cancelReason' => $item->cancel_reason,
            ])->values()->all(),
        ];
    }

    private function stockStatus(MenuItem $item): array
    {
        if ($item->availability !== 'available') {
            return ['status' => 'out', 'left' => 0];
        }

        if (! $item->stock_tracked || ! $item->recipe || $item->recipe->lines->isEmpty()) {
            return ['status' => 'in', 'left' => null];
        }

        $possible = $item->recipe->lines->map(function ($line) {
            if ((float) $line->qty <= 0) return PHP_INT_MAX;
            return floor((float) ($line->ingredient?->current_stock ?? 0) / (float) $line->qty);
        })->min();

        return [
            'status' => $possible <= 0 ? 'out' : ($possible <= 5 ? 'low' : 'in'),
            'left' => $possible === PHP_INT_MAX ? null : (int) $possible,
        ];
    }

    private function readyAlerts(Order $order): array
    {
        return $order->items
            ->where('status', 'ready')
            ->map(fn (OrderItem $item) => [
                'id' => 'item-' . $item->id,
                'itemId' => $item->id,
                'table' => $order->table?->name ?? $order->token ?? 'Takeaway',
                'item' => $item->menuItem?->name ?? 'Item',
                'qty' => (int) $item->qty,
                'station' => $item->station?->name ?? $item->menuItem?->station?->name ?? 'Kitchen',
            ])
            ->values()
            ->all();
    }

    private function tableId(?string $name): ?int
    {
        return $name ? RestaurantTable::where('name', $name)->orWhere('code', $name)->value('id') : null;
    }

    private function nextOrderCode(): string
    {
        $year = now()->format('Y');
        $last = Order::where('code', 'like', "ORD-{$year}-%")->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 1;

        return 'ORD-' . $year . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function nextKotCode(): string
    {
        $last = Kot::where('code', 'like', 'KOT-%')->orderByDesc('id')->value('code');
        $next = $last ? ((int) str($last)->afterLast('-')->toString()) + 1 : 1;

        return 'KOT-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function orderRelations(): array
    {
        return ['table', 'customer', 'waiter', 'items.menuItem.station', 'items.station', 'kots', 'invoice.payments'];
    }

    private function operator(): array
    {
        $employee = request()->user()?->employee;

        return [
            'name' => $employee?->name ?? request()->user()?->name ?? 'Operator',
            'role' => $employee?->role?->name ?? 'Cashier',
            'terminal' => 'POS-01',
            'discountLimitPct' => 10,
        ];
    }
}
