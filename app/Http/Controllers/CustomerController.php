<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers', ['customerModule' => $this->payload()]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->payload());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $customer = Customer::create($this->attributes($data) + [
            'joined_date' => now()->toDateString(),
            'loyalty_points' => 0,
            'is_vip' => false,
        ]);

        return response()->json([
            'customer' => $this->customerResource($customer->fresh(['orders.invoice', 'reservations'])),
            'message' => "{$customer->name} added",
        ], 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $this->validated($request, $customer);

        $customer->update($this->attributes($data));

        return response()->json([
            'customer' => $this->customerResource($customer->fresh(['orders.invoice', 'reservations'])),
            'message' => "{$customer->name} updated",
        ]);
    }

    public function vip(Customer $customer): JsonResponse
    {
        $customer->update(['is_vip' => ! $customer->is_vip]);

        return response()->json([
            'customer' => $this->customerResource($customer->fresh(['orders.invoice', 'reservations'])),
            'message' => $customer->is_vip ? "{$customer->name} marked VIP" : "{$customer->name} removed from VIP",
        ]);
    }

    public function loyalty(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $customer->update([
            'loyalty_points' => max(0, $customer->loyalty_points + $data['points']),
        ]);

        return response()->json([
            'customer' => $this->customerResource($customer->fresh(['orders.invoice', 'reservations'])),
            'log' => [
                'at' => now()->format('d/m/Y'),
                'text' => 'Manual adjustment: ' . ($data['points'] > 0 ? '+' : '') . $data['points'] . ' pts - ' . $data['reason'],
            ],
            'message' => "Loyalty points updated for {$customer->name}",
        ]);
    }

    public function note(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update(['notes' => $data['notes'] ?? null]);

        return response()->json([
            'customer' => $this->customerResource($customer->fresh(['orders.invoice', 'reservations'])),
            'message' => 'Note saved',
        ]);
    }

    private function payload(): array
    {
        return [
            'venue' => [
                'name' => config('app.name', 'Restaurant'),
                'branch' => 'Ichapur Main Branch',
            ],
            'customers' => Customer::with(['orders.invoice', 'reservations'])
                ->latest('id')
                ->get()
                ->map(fn ($customer) => $this->customerResource($customer))
                ->values()
                ->all(),
            'tags' => ['Regular', 'VIP', 'Family', 'Corporate', 'Foodie', 'Loyal'],
        ];
    }

    private function customerResource(Customer $customer): array
    {
        $orders = $customer->orders->sortByDesc('started_at');
        $paidOrders = $orders->filter(fn ($order) => $order->invoice);
        $spend = (int) $paidOrders->sum(fn ($order) => $order->invoice?->grandTotal() ?? 0);
        $visits = $orders->count();

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'birthday' => $customer->birthday?->toDateString(),
            'anniversary' => $customer->anniversary?->toDateString(),
            'address' => $customer->address,
            'gstin' => $customer->gstin,
            'tags' => $customer->tags ?? [],
            'visits' => $visits,
            'spend' => $spend,
            'avgBill' => $visits ? (int) round($spend / $visits) : 0,
            'lastVisit' => $orders->first()?->started_at?->toDateString() ?? '—',
            'points' => $customer->loyalty_points,
            'vip' => $customer->is_vip,
            'favoriteItems' => [],
            'recentOrders' => $orders->take(5)->map(fn ($order) => [
                'code' => $order->code,
                'date' => $order->started_at?->toDateString() ?? $order->created_at?->toDateString(),
                'amount' => $order->invoice?->grandTotal() ?? 0,
            ])->values()->all(),
            'reservationsCount' => $customer->reservations->count(),
            'notes' => $customer->notes,
            'allergies' => $customer->allergies,
            'joinedDate' => $customer->joined_date?->toDateString() ?? $customer->created_at?->toDateString(),
        ];
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'anniversary' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'tags' => ['present', 'array'],
            'tags.*' => ['string', 'max:50'],
        ]);
    }

    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'anniversary' => $data['anniversary'] ?? null,
            'address' => $data['address'] ?? null,
            'gstin' => $data['gstin'] ?? null,
            'tags' => $data['tags'] ?? [],
        ];
    }
}
