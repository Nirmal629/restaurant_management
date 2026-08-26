<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RealtimeNotifier;
use App\Services\StockConsumptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $order = $this->currentOrder($request);

        return view('billing', ['billingModule' => $this->payload($order)]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->payload($this->currentOrder($request)));
    }

    public function discount(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['nullable', Rule::in(['pct', 'amt'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $invoice->update([
            'bill_discount_mode' => $data['mode'] ?? null,
            'bill_discount_value' => $data['value'] ?? null,
            'bill_discount_reason' => $data['reason'] ?? null,
            'bill_discount_approved_by' => $this->employeeId($request),
        ]);

        return $this->billingResponse($invoice->fresh(['order.items.menuItem', 'payments', 'refunds']));
    }

    public function item(Request $request, OrderItem $item, StockConsumptionService $stock): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['discount', 'complimentary', 'cancel'])],
            'mode' => ['nullable', Rule::in(['pct', 'amt'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['action'] === 'discount') {
            $amount = $this->discountAmount($item, $data['mode'] ?? 'amt', (float) ($data['value'] ?? 0));
            $item->update([
                'bill_status' => $amount > 0 ? 'discounted' : 'normal',
                'bill_discount_amount' => $amount,
                'comp_reason' => null,
                'comp_by' => null,
            ]);
        }

        if ($data['action'] === 'complimentary') {
            $item->update([
                'bill_status' => 'complimentary',
                'bill_discount_amount' => 0,
                'comp_reason' => $data['reason'] ?? 'Manager approved',
                'comp_by' => $this->employeeId($request),
            ]);
        }

        if ($data['action'] === 'cancel') {
            if ($item->status !== 'served') {
                $stock->reverse($item, 'SALE_CANCEL_REVERSAL', $request->user()?->employee);
            }

            $item->update([
                'status' => 'cancelled',
                'cancel_reason' => $data['reason'] ?? 'Cancelled during billing',
            ]);
        }

        return $this->billingResponse($item->order->invoice->fresh(['order.items.menuItem', 'payments', 'refunds']));
    }

    public function adjustments(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'serviceRemoved' => ['sometimes', 'boolean'],
            'loyaltyPoints' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'loyaltyAmount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'couponCode' => ['sometimes', 'nullable', 'string', 'max:40'],
            'couponAmount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'isGstInvoice' => ['sometimes', 'boolean'],
            'customerId' => ['sometimes', 'nullable', 'integer', 'exists:customers,id'],
            'customer' => ['sometimes', 'array'],
            'customer.name' => ['required_with:customer', 'string', 'max:255'],
            'customer.phone' => ['required_with:customer', 'string', 'max:20'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.gstin' => ['nullable', 'string', 'max:30'],
            'customer.businessName' => ['nullable', 'string', 'max:255'],
            'customer.address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($invoice, $data) {
            $updates = [];
            foreach ([
                'serviceRemoved' => 'service_removed',
                'loyaltyPoints' => 'loyalty_points_redeemed',
                'loyaltyAmount' => 'loyalty_amount',
                'couponCode' => 'coupon_code',
                'couponAmount' => 'coupon_amount',
                'isGstInvoice' => 'is_gst_invoice',
            ] as $input => $column) {
                if (array_key_exists($input, $data)) {
                    $updates[$column] = $data[$input];
                }
            }

            if ($updates) {
                $invoice->update($updates);
            }

            if (array_key_exists('customerId', $data)) {
                $invoice->order()->update(['customer_id' => $data['customerId']]);
            }

            if (isset($data['customer'])) {
                $customer = Customer::updateOrCreate(
                    ['phone' => $data['customer']['phone']],
                    [
                        'name' => $data['customer']['name'],
                        'email' => $data['customer']['email'] ?? null,
                        'gstin' => $data['customer']['gstin'] ?? null,
                        'business_name' => $data['customer']['businessName'] ?? null,
                        'address' => $data['customer']['address'] ?? null,
                        'joined_date' => now()->toDateString(),
                    ]
                );
                $invoice->order()->update(['customer_id' => $customer->id]);
            }
        });

        return $this->billingResponse($invoice->fresh(['order.customer', 'order.items.menuItem', 'payments', 'refunds']));
    }

    public function applyCoupon(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $coupon = Coupon::where('code', strtoupper(trim($data['code'])))->first();

        if (! $coupon) {
            return response()->json(['message' => 'Coupon is invalid or expired.'], 422);
        }

        if ($message = $coupon->validateFor($invoice)) {
            return response()->json(['message' => $message], 422);
        }

        $amount = $coupon->discountFor($invoice->couponBaseAmount());
        if ($amount <= 0) {
            return response()->json(['message' => 'Coupon has no discount for this bill.'], 422);
        }

        DB::transaction(function () use ($invoice, $coupon, $amount) {
            $invoice->update([
                'coupon_code' => $coupon->code,
                'coupon_amount' => $amount,
            ]);

            CouponRedemption::updateOrCreate(
                ['coupon_id' => $coupon->id, 'invoice_id' => $invoice->id],
                ['customer_id' => $invoice->order?->customer_id, 'amount' => $amount, 'redeemed_at' => now()]
            );
        });

        return $this->billingResponse($this->refreshStatus($invoice->fresh(['order.customer', 'order.items.menuItem', 'payments', 'refunds'])));
    }

    public function removeCoupon(Invoice $invoice): JsonResponse
    {
        DB::transaction(function () use ($invoice) {
            if ($invoice->coupon_code) {
                CouponRedemption::where('invoice_id', $invoice->id)->delete();
            }

            $invoice->update([
                'coupon_code' => null,
                'coupon_amount' => 0,
            ]);
        });

        return $this->billingResponse($this->refreshStatus($invoice->fresh(['order.customer', 'order.items.menuItem', 'payments', 'refunds'])));
    }

    public function payment(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'method' => ['required', Rule::in(['cash', 'upi', 'credit', 'debit', 'wallet', 'bank', 'other'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tendered' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        if ($invoice->voided) {
            return response()->json(['message' => 'Voided invoices cannot accept payments.'], 422);
        }

        $amount = min((float) $data['amount'], $invoice->dueAmount());
        Payment::create([
            'invoice_id' => $invoice->id,
            'method' => $data['method'],
            'amount' => $amount,
            'tendered' => $data['tendered'] ?? $amount,
            'reference' => $data['reference'] ?? null,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        return $this->billingResponse($this->refreshStatus($invoice->fresh(['order.table', 'order.items.menuItem', 'payments', 'refunds'])));
    }

    public function complete(Invoice $invoice): JsonResponse
    {
        if ($invoice->dueAmount() > 0) {
            return response()->json(['message' => 'Cannot complete payment while amount is still due.'], 422);
        }

        $this->refreshStatus($invoice);
        $invoice->order()->update(['status' => 'paid']);

        return $this->billingResponse($invoice->fresh(['order.table', 'order.items.menuItem', 'payments', 'refunds']));
    }

    public function refund(Request $request, Invoice $invoice, StockConsumptionService $stock): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['full', 'partial', 'item'])],
            'method' => ['required', Rule::in(['cash', 'upi', 'credit', 'debit', 'wallet', 'bank', 'other'])],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['array'],
            'items.*' => ['integer', 'exists:order_items,id'],
        ]);

        $amount = $this->refundAmount($invoice, $data);
        if ($amount <= 0 || $amount > ($invoice->paidTotal() - $invoice->refundedTotal())) {
            return response()->json(['message' => 'Refund amount is not valid.'], 422);
        }

        DB::transaction(function () use ($invoice, $data, $amount, $request) {
            Refund::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $data['method'],
                'mode' => $data['mode'],
                'reason' => $data['reason'],
                'approved_by' => $this->employeeId($request),
                'refunded_at' => now(),
            ]);

            if ($data['mode'] === 'item') {
                $items = OrderItem::whereIn('id', $data['items'] ?? [])->get();
                foreach ($items as $item) {
                    $stock->reverse($item, 'SALE_REFUND_REVERSAL', $request->user()?->employee);
                }

                OrderItem::whereIn('id', $items->pluck('id'))->update([
                    'bill_status' => 'refunded',
                    'refund_reason' => $data['reason'],
                ]);
            }
        });

        return $this->billingResponse($this->refreshStatus($invoice->fresh(['order.items.menuItem', 'payments', 'refunds'])));
    }

    public function void(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $invoice->update([
            'voided' => true,
            'void_reason' => $data['reason'],
            'status' => 'voided',
        ]);
        $invoice->order()->update(['status' => 'cancelled']);

        return $this->billingResponse($invoice->fresh(['order.items.menuItem', 'payments', 'refunds']));
    }

    public function close(Invoice $invoice): JsonResponse
    {
        if ($invoice->dueAmount() > 0 && ! $invoice->voided) {
            return response()->json(['message' => 'Settle or void the invoice before closing the table.'], 422);
        }

        $invoice->order?->table?->update(['status' => 'cleaning']);
        $invoice->order()->update(['status' => $invoice->voided ? 'cancelled' : 'completed']);

        return $this->billingResponse($invoice->fresh(['order.table', 'order.items.menuItem', 'payments', 'refunds']));
    }

    private function payload(?Order $order): array
    {
        if (! $order) {
            return [
                'empty' => true,
                'message' => 'No active bill found.',
                'orders' => $this->billingQueue(),
                'history' => $this->invoiceHistory(),
                'operator' => $this->operator(),
                'paymentMethods' => $this->paymentMethods(),
                'customers' => $this->customerOptions(),
            ];
        }

        $invoice = $this->ensureInvoice($order);
        $freshInvoice = $invoice->fresh(['order.table', 'order.customer', 'order.waiter', 'order.items.menuItem', 'payments', 'refunds']);

        return [
            'venue' => ['name' => config('app.name', 'Restaurant'), 'branch' => $order->table?->area ?? 'Main Branch'],
            'operator' => $this->operator(),
            'orders' => $this->billingQueue(),
            'history' => $this->invoiceHistory(),
            ...$this->invoiceResource($freshInvoice),
            'availableCoupons' => $this->availableCoupons($freshInvoice),
            'paymentMethods' => $this->paymentMethods(),
            'customers' => $this->customerOptions(),
        ];
    }

    private function currentOrder(Request $request): ?Order
    {
        $query = Order::with(['table', 'customer', 'waiter', 'items.menuItem', 'invoice.payments', 'invoice.refunds']);

        if ($request->integer('order')) {
            $order = $query->whereKey($request->integer('order'))->first();

            return $order && $this->canEnterBilling($order) ? $order : null;
        }

        return $query
            ->whereIn('status', ['open', 'billing'])
            ->latest('id')
            ->get()
            ->first(fn (Order $order) => $this->canEnterBilling($order));
    }

    private function ensureInvoice(Order $order): Invoice
    {
        if (! $this->canEnterBilling($order)) {
            abort(422, 'Serve or cancel all kitchen items before billing.');
        }

        if ($order->status === 'open') {
            $order->update(['status' => 'billing']);
            $order->table?->update(['status' => 'billing']);
        }

        return $order->invoice ?: Invoice::create([
            'code' => $this->nextInvoiceCode(),
            'order_id' => $order->id,
            'status' => 'generated',
        ]);
    }

    private function canEnterBilling(Order $order): bool
    {
        if (in_array($order->status, ['billing', 'paid'], true)) {
            return true;
        }

        if ($order->status !== 'open' || $order->items->isEmpty()) {
            return false;
        }

        return $order->items->whereNotIn('status', ['served', 'cancelled'])->isEmpty();
    }

    private function invoiceResource(Invoice $invoice): array
    {
        return [
            'invoice' => [
                'id' => $invoice->id,
                'code' => $invoice->code,
                'status' => $invoice->computeStatus(),
                'voided' => $invoice->voided,
                'voidReason' => $invoice->void_reason,
            ],
            'order' => [
                'id' => $invoice->order->id,
                'code' => $invoice->order->code,
                'type' => $invoice->order->type,
                'table' => $invoice->order->table?->name ?? 'Takeaway',
                'guests' => $invoice->order->guests,
                'waiter' => $invoice->order->waiter?->name,
                'startedMinutesAgo' => $invoice->order->started_at ? (int) $invoice->order->started_at->diffInMinutes(now()) : 0,
            ],
            'customer' => $this->customerResource($invoice->order->customer),
            'items' => $invoice->order->items->map(fn ($item) => $this->itemResource($item))->values()->all(),
            'charges' => [
                'taxMode' => $invoice->tax_mode,
                'cgstRate' => (float) $invoice->cgst_rate,
                'sgstRate' => (float) $invoice->sgst_rate,
                'serviceRate' => (float) $invoice->service_rate,
                'serviceRemoved' => $invoice->service_removed,
                'serviceLocked' => true,
            ],
            'billDiscount' => $invoice->bill_discount_mode ? [
                'mode' => $invoice->bill_discount_mode,
                'value' => (float) $invoice->bill_discount_value,
                'reason' => $invoice->bill_discount_reason,
                'approvedBy' => $invoice->billDiscountApprover?->name,
            ] : null,
            'loyaltyRedeemed' => $invoice->loyalty_points_redeemed ? ['points' => $invoice->loyalty_points_redeemed, 'amount' => (float) $invoice->loyalty_amount] : null,
            'coupon' => $invoice->coupon_code ? ['code' => $invoice->coupon_code, 'amount' => (float) $invoice->coupon_amount] : null,
            'payments' => $invoice->payments->map(fn ($payment) => $this->paymentResource($payment))->values()->all(),
            'refunds' => $invoice->refunds->map(fn ($refund) => $this->refundResource($refund))->values()->all(),
            'totals' => [
                'subtotal' => $invoice->subtotal(),
                'itemDiscountTotal' => $invoice->itemDiscountTotal(),
                'complimentaryTotal' => $invoice->complimentaryTotal(),
                'billDiscountAmount' => $invoice->billDiscountAmount(),
                'loyaltyAmount' => (float) $invoice->loyalty_amount,
                'couponAmount' => (float) $invoice->coupon_amount,
                'totalDeductions' => $invoice->totalDeductions(),
                'taxableAmount' => $invoice->taxableAmount(),
                'cgstAmount' => $invoice->cgstAmount(),
                'sgstAmount' => $invoice->sgstAmount(),
                'serviceAmount' => $invoice->serviceAmount(),
                'grandTotalRaw' => $invoice->grandTotalRaw(),
                'grandTotal' => $invoice->grandTotal(),
                'roundOff' => $invoice->roundOff(),
                'paidTotal' => $invoice->paidTotal(),
                'dueAmount' => $invoice->dueAmount(),
                'refundedTotal' => $invoice->refundedTotal(),
            ],
        ];
    }

    private function itemResource(OrderItem $item): array
    {
        $status = $item->status === 'cancelled' ? 'cancelled' : $item->bill_status;
        return [
            'uid' => $item->id,
            'id' => $item->id,
            'name' => $item->menuItem?->name ?? 'Item',
            'qty' => (int) $item->qty,
            'rate' => (float) $item->unit_price + $item->modifierTotal(),
            'amount' => $item->grossAmount(),
            'discount' => (float) $item->bill_discount_amount,
            'status' => $status,
            'note' => $item->note,
            'kot' => $item->kot_round,
            'orderedAt' => $item->sent_at?->format('H:i') ?? '',
            'compReason' => $item->comp_reason,
            'cancelReason' => $item->cancel_reason,
            'refundReason' => $item->refund_reason,
            'refundAmount' => (float) $item->refund_amount,
        ];
    }

    private function billingResponse(Invoice $invoice): JsonResponse
    {
        app(RealtimeNotifier::class)->touch(['billing', 'orders', 'tables', 'pos']);

        $fresh = $invoice->fresh(['order.table', 'order.customer', 'order.waiter', 'order.items.menuItem', 'payments', 'refunds']);

        return response()->json([
            ...$this->invoiceResource($fresh),
            'message' => 'Billing updated',
            'availableCoupons' => $this->availableCoupons($fresh),
            'orders' => $this->billingQueue(),
            'history' => $this->invoiceHistory(),
        ]);
    }

    private function billingQueue(): array
    {
        return Order::with(['table', 'customer', 'waiter', 'items.menuItem', 'invoice.payments', 'invoice.refunds'])
            ->whereIn('status', ['open', 'billing'])
            ->latest('id')
            ->get()
            ->filter(fn (Order $order) => $this->canEnterBilling($order))
            ->map(function (Order $order) {
                $invoice = $order->invoice;
                $total = $invoice ? $invoice->grandTotal() : $order->subtotal();
                $paid = $invoice ? $invoice->paidTotal() : 0;
                $due = $invoice ? $invoice->dueAmount() : $total;

                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'invoiceCode' => $invoice?->code,
                    'status' => $invoice?->computeStatus() ?? 'open',
                    'orderStatus' => $order->status,
                    'table' => $order->table?->name ?? 'Takeaway',
                    'type' => $order->type,
                    'customer' => $order->customer?->name ?? 'Walk-in Customer',
                    'waiter' => $order->waiter?->name,
                    'items' => $order->items->where('status', '!=', 'cancelled')->count(),
                    'total' => round($total, 2),
                    'paid' => round($paid, 2),
                    'due' => round($due, 2),
                    'startedMinutesAgo' => $order->started_at ? (int) $order->started_at->diffInMinutes(now()) : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function invoiceHistory(): array
    {
        return Invoice::with(['order.table', 'order.customer', 'payments', 'refunds'])
            ->where(function ($query) {
                $query->whereHas('order', fn ($q) => $q->whereIn('status', ['completed', 'cancelled']))
                    ->orWhereIn('status', ['voided', 'refunded', 'partially_refunded']);
            })
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'orderId' => $invoice->order_id,
                'code' => $invoice->code,
                'orderCode' => $invoice->order?->code,
                'status' => $invoice->computeStatus(),
                'orderStatus' => $invoice->order?->status,
                'table' => $invoice->order?->table?->name ?? 'Takeaway',
                'customer' => $invoice->order?->customer?->name ?? 'Walk-in Customer',
                'total' => $invoice->grandTotal(),
                'paid' => $invoice->paidTotal(),
                'refunded' => $invoice->refundedTotal(),
                'at' => $invoice->updated_at?->format('d/m/Y H:i'),
            ])
            ->values()
            ->all();
    }

    private function customerOptions(): array
    {
        return Customer::orderBy('name')->limit(20)->get()->map(fn ($c) => [
            'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'visits' => $c->visits(), 'spend' => 0, 'points' => $c->loyalty_points ?? 0,
        ])->values()->all();
    }

    private function availableCoupons(Invoice $invoice): array
    {
        return Coupon::withCount('redemptions')
            ->where('active', true)
            ->orderBy('code')
            ->get()
            ->filter(fn (Coupon $coupon) => $coupon->validateFor($invoice) === null)
            ->map(fn (Coupon $coupon) => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'amount' => $coupon->discountFor($invoice->couponBaseAmount()),
                'minBillAmount' => (float) $coupon->min_bill_amount,
                'maxDiscountAmount' => $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : null,
                'expiresAt' => $coupon->expires_at?->toDateString(),
                'walkinAllowed' => $coupon->walkin_allowed,
                'redemptions' => $coupon->redemptions_count,
                'usageLimit' => $coupon->usage_limit,
            ])
            ->values()
            ->all();
    }

    private function refreshStatus(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => $invoice->computeStatus()]);
        return $invoice->fresh();
    }

    private function discountAmount(OrderItem $item, string $mode, float $value): float
    {
        $raw = $mode === 'pct' ? $item->grossAmount() * $value / 100 : $value;
        return round(min($raw, $item->grossAmount()), 2);
    }

    private function refundAmount(Invoice $invoice, array $data): float
    {
        if ($data['mode'] === 'full') return $invoice->paidTotal() - $invoice->refundedTotal();
        if ($data['mode'] === 'partial') return (float) ($data['amount'] ?? 0);

        return OrderItem::whereIn('id', $data['items'] ?? [])
            ->get()
            ->sum(fn (OrderItem $item) => $item->lineTotal());
    }

    private function employeeId(Request $request): ?int
    {
        return $request->user()?->employee?->id;
    }

    private function operator(): array
    {
        $employee = auth()->user()?->employee?->loadMissing('role');
        return [
            'name' => $employee?->name ?? auth()->user()?->name ?? 'Cashier',
            'role' => $employee?->role?->name ?? 'Cashier',
            'initials' => collect(explode(' ', $employee?->name ?? auth()->user()?->name ?? 'C'))->filter()->map(fn ($w) => strtoupper($w[0]))->take(2)->implode(''),
            'terminal' => 'Terminal 1',
            'shift' => ucfirst($employee?->shift ?? 'Full Day'),
            'discountLimitPct' => 10,
        ];
    }

    private function customerResource(?Customer $customer): array
    {
        return [
            'id' => $customer?->id,
            'name' => $customer?->name ?? 'Walk-in Customer',
            'phone' => $customer?->phone ?? '',
            'email' => $customer?->email ?? '',
            'gstin' => $customer?->gstin ?? '',
            'businessName' => $customer?->business_name ?? '',
            'address' => $customer?->address ?? '',
            'loyalty' => ['points' => $customer?->loyalty_points ?? 0, 'valuePerPoint' => 1],
        ];
    }

    private function paymentResource(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'method' => $payment->method,
            'label' => ucfirst($payment->method),
            'amount' => (float) $payment->amount,
            'tendered' => (float) ($payment->tendered ?? $payment->amount),
            'reference' => $payment->reference,
            'at' => $payment->paid_at?->format('H:i'),
            'status' => $payment->status,
        ];
    }

    private function refundResource(Refund $refund): array
    {
        return [
            'id' => $refund->id,
            'amount' => (float) $refund->amount,
            'method' => $refund->method,
            'mode' => $refund->mode,
            'reason' => $refund->reason,
            'approvedBy' => $refund->approver?->name ?? 'Manager',
            'at' => $refund->refunded_at?->format('H:i'),
        ];
    }

    private function paymentMethods(): array
    {
        return collect(['cash', 'upi', 'credit', 'debit', 'wallet', 'bank', 'other'])
            ->map(fn ($key) => ['key' => $key, 'label' => ucfirst($key)])
            ->all();
    }

    private function nextInvoiceCode(): string
    {
        $last = Invoice::where('code', 'like', 'INV-%')->latest('id')->value('code');
        $number = $last ? ((int) substr($last, -6)) + 1 : 1;

        return 'INV-' . now()->format('Y') . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
