<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RealtimeNotifier;
use App\Services\StockConsumptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders', ['ordersPayload' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function status(Request $request, Order $order, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'billing', 'paid', 'completed', 'cancelled'])],
        ]);

        if ($data['status'] === 'billing' && $order->items()->whereNotIn('status', ['served', 'cancelled'])->exists()) {
            return response()->json(['message' => 'Serve or cancel all kitchen items before moving to billing.'], 422);
        }

        $order->update(['status' => $data['status']]);

        if ($data['status'] === 'billing') {
            $order->table?->update(['status' => 'billing']);
        }

        if ($data['status'] === 'cancelled') {
            $order->items()->whereNotIn('status', ['served', 'cancelled'])->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Order cancelled',
            ]);
            $order->table?->update(['status' => 'cleaning']);
        }

        $realtime->touch(['orders', 'kitchen', 'billing', 'tables', 'pos']);

        return response()->json([
            'order' => $this->orderResource($order->fresh($this->relations())),
            'orders' => $this->orders(),
            'summary' => $this->summary(),
            'message' => 'Order updated',
        ]);
    }

    public function itemStatus(Request $request, OrderItem $item, StockConsumptionService $stock, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'preparing', 'ready', 'served', 'cancelled'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['status'] === 'cancelled' && $item->status !== 'served') {
            $stock->reverse($item, 'SALE_CANCEL_REVERSAL', $request->user()?->employee);
        }

        $item->update([
            'status' => $data['status'],
            'ready_at' => $data['status'] === 'ready' ? now() : $item->ready_at,
            'cancel_reason' => $data['status'] === 'cancelled' ? ($data['reason'] ?? 'Cancelled from orders') : $item->cancel_reason,
        ]);

        $realtime->touch(['orders', 'kitchen', 'billing', 'tables', 'pos', 'inventory', 'menu']);

        return response()->json([
            'order' => $this->orderResource($item->order->fresh($this->relations())),
            'orders' => $this->orders(),
            'summary' => $this->summary(),
            'message' => 'Item updated',
        ]);
    }

    private function payload(): array
    {
        $orders = $this->orders();

        return [
            'orders' => $orders,
            'summary' => $this->summary(),
            'activeOrderId' => $orders[0]['id'] ?? null,
        ];
    }

    private function orders(): array
    {
        return Order::with($this->relations())
            ->latest('id')
            ->limit(80)
            ->get()
            ->map(fn (Order $order) => $this->orderResource($order))
            ->values()
            ->all();
    }

    private function summary(): array
    {
        $orders = Order::with(['items', 'invoice'])->get();

        return [
            'active' => $orders->whereIn('status', ['open', 'billing'])->count(),
            'kitchen' => $orders->filter(fn (Order $order) => $order->items->whereNotIn('status', ['served', 'cancelled'])->isNotEmpty())->count(),
            'billing' => $orders->where('status', 'billing')->count(),
            'paidToday' => $orders->filter(fn (Order $order) => $order->invoice && $order->invoice->paidTotal() > 0 && $order->updated_at->isToday())->count(),
        ];
    }

    private function orderResource(Order $order): array
    {
        $items = $order->items->map(fn (OrderItem $item) => [
            'id' => $item->id,
            'name' => $item->menuItem?->name ?? 'Item',
            'qty' => (int) $item->qty,
            'rate' => (float) $item->unit_price + $item->modifierTotal(),
            'amount' => $item->grossAmount(),
            'status' => $item->status,
            'billStatus' => $item->bill_status,
            'station' => $item->station?->name ?? $item->menuItem?->station?->name ?? 'Kitchen',
            'kotRound' => $item->kot_round,
            'note' => $item->note,
            'sentAt' => $item->sent_at?->format('H:i'),
            'readyAt' => $item->ready_at?->format('H:i'),
            'cancelReason' => $item->cancel_reason,
        ])->values();

        return [
            'id' => $order->id,
            'code' => $order->code,
            'type' => $order->type,
            'status' => $order->status,
            'table' => $order->table?->name ?? 'Takeaway',
            'customer' => $order->customer?->name ?? 'Walk-in Customer',
            'waiter' => $order->waiter?->name ?? '-',
            'guests' => $order->guests ?? 0,
            'token' => $order->token,
            'startedAt' => $order->started_at?->format('H:i'),
            'startedMinutesAgo' => $order->started_at ? (int) $order->started_at->diffInMinutes(now()) : 0,
            'items' => $items->all(),
            'itemCount' => $items->where('status', '!=', 'cancelled')->count(),
            'kitchenOpen' => $items->whereNotIn('status', ['served', 'cancelled'])->count(),
            'total' => $order->invoice?->grandTotal() ?? $order->subtotal(),
            'paid' => $order->invoice?->paidTotal() ?? 0,
            'due' => $order->invoice?->dueAmount() ?? ($order->invoice ? 0 : $order->subtotal()),
            'invoiceCode' => $order->invoice?->code,
            'invoiceStatus' => $order->invoice?->computeStatus(),
            'kots' => $order->kots->map(fn ($kot) => [
                'code' => $kot->code,
                'round' => $kot->round,
                'sentAt' => $kot->sent_at?->format('H:i'),
                'printer' => $kot->printer,
            ])->values()->all(),
        ];
    }

    private function relations(): array
    {
        return ['table', 'customer', 'waiter', 'items.menuItem.station', 'items.station', 'kots', 'invoice.payments', 'invoice.refunds'];
    }
}
