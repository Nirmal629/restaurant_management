<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RealtimeNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KdsController extends Controller
{
    public function index(): View
    {
        return view('kds', ['kdsPayload' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function itemStatus(Request $request, OrderItem $item, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'preparing', 'ready', 'served', 'cancelled'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $item->update([
            'status' => $data['status'],
            'ready_at' => $data['status'] === 'ready' ? now() : $item->ready_at,
            'cancel_reason' => $data['status'] === 'cancelled' ? ($data['reason'] ?? 'Cancelled from KDS') : $item->cancel_reason,
        ]);

        $realtime->touch(['kitchen', 'orders', 'pos', 'billing', 'tables']);

        return response()->json([...$this->payload(), 'message' => 'Kitchen item updated']);
    }

    public function orderStatus(Request $request, Order $order, RealtimeNotifier $realtime): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'preparing', 'ready', 'served'])],
        ]);

        $itemStatus = $data['status'] === 'served' ? 'served' : $data['status'];
        $updates = ['status' => $itemStatus];
        if ($itemStatus === 'ready') {
            $updates['ready_at'] = now();
        }

        $order->items()->whereNotIn('status', ['cancelled', 'served'])->update($updates);
        $realtime->touch(['kitchen', 'orders', 'pos', 'billing', 'tables']);

        return response()->json([...$this->payload(), 'message' => 'Kitchen ticket updated']);
    }

    private function payload(): array
    {
        $orders = Order::with(['table', 'customer', 'waiter', 'items.menuItem.station', 'items.station', 'kots'])
            ->whereIn('status', ['open', 'billing'])
            ->whereHas('items', fn ($query) => $query->whereNotIn('status', ['cancelled', 'served']))
            ->latest('id')
            ->limit(80)
            ->get();

        $employee = request()->user()?->employee;
        $operatorName = $employee?->name ?? request()->user()?->name ?? 'Kitchen';

        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => 'Main Branch'],
            'operator' => [
                'name' => $operatorName,
                'role' => $employee?->role?->name ?? 'Kitchen',
                'initials' => str($operatorName)->explode(' ')->map(fn ($part) => str($part)->substr(0, 1))->take(2)->implode(''),
                'shift' => str($employee?->shift ?? 'full day')->headline()->toString(),
            ],
            'tickets' => $orders->flatMap(fn (Order $order) => $this->tickets($order))->values()->all(),
        ];
    }

    private function tickets(Order $order)
    {
        return $order->items
            ->whereNotIn('status', ['cancelled', 'served'])
            ->groupBy('kot_round')
            ->map(function ($items, $round) use ($order) {
                $first = $items->first();
                $placedAt = $first?->sent_at ?? $order->started_at ?? $order->created_at;

                return [
                    'key' => $order->id . '-' . ($round ?: $order->id),
                    'kot' => $round ?: $order->id,
                    'round' => (int) $round,
                    'orderId' => $order->id,
                    'orderCode' => $order->code,
                    'orderType' => $order->type,
                    'table' => $order->table?->name ?? 'Takeaway',
                    'token' => $order->token,
                    'waiter' => $order->waiter?->name,
                    'guests' => $order->guests,
                    'placedAt' => $placedAt?->getTimestampMs() ?? now()->getTimestampMs(),
                    'status' => $this->ticketStatus($items),
                    'priority' => 'normal',
                    'waiterNotified' => false,
                    'items' => $items->map(fn (OrderItem $item) => [
                        'uid' => $item->id,
                        'id' => $item->id,
                        'name' => $item->menuItem?->name ?? 'Item',
                        'qty' => (int) $item->qty,
                        'station' => str($item->station?->name ?? $item->menuItem?->station?->name ?? 'Kitchen')->slug()->toString(),
                        'course' => 'main',
                        'fire' => 'fire',
                        'status' => in_array($item->status, ['accepted', 'preparing'], true) ? 'pending' : $item->status,
                        'backendStatus' => $item->status,
                        'note' => collect([$item->variant_label, $item->note])->filter()->join(' - '),
                        'modifiers' => collect($item->modifiers ?? [])->pluck('label')->filter()->values()->all(),
                        'readyAt' => $item->ready_at?->getTimestampMs(),
                    ])->values()->all(),
                ];
            });
    }

    private function ticketStatus($items): string
    {
        $statuses = $items->pluck('status');

        if ($statuses->every(fn ($status) => $status === 'ready')) {
            return 'ready';
        }

        if ($statuses->contains('preparing')) {
            return 'preparing';
        }

        if ($statuses->contains('accepted')) {
            return 'accepted';
        }

        return 'new';
    }
}
