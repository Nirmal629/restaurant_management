<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'code', 'order_id', 'status', 'tax_mode', 'cgst_rate', 'sgst_rate', 'service_rate', 'service_removed',
        'bill_discount_mode', 'bill_discount_value', 'bill_discount_reason', 'bill_discount_approved_by',
        'loyalty_points_redeemed', 'loyalty_amount', 'coupon_code', 'coupon_amount',
        'is_gst_invoice', 'voided', 'void_reason',
    ];

    protected $casts = [
        'service_removed' => 'boolean',
        'is_gst_invoice' => 'boolean',
        'voided' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function billDiscountApprover()
    {
        return $this->belongsTo(Employee::class, 'bill_discount_approved_by');
    }

    /* -----------------------------------------------------------------
       Money chain — mirrors resources/js/billing/store.js exactly, so the
       eventual server-rendered Billing page produces identical figures to
       the frontend design.
       ----------------------------------------------------------------- */

    public function subtotal(): float
    {
        return $this->order->subtotal();
    }

    public function itemDiscountTotal(): float
    {
        return $this->order->itemDiscountTotal();
    }

    public function complimentaryTotal(): float
    {
        return $this->order->complimentaryTotal();
    }

    public function billDiscountAmount(): float
    {
        if (! $this->bill_discount_mode) {
            return 0.0;
        }

        $base = $this->subtotal() - $this->itemDiscountTotal() - $this->complimentaryTotal();
        $raw = $this->bill_discount_mode === 'pct'
            ? $base * $this->bill_discount_value / 100
            : (float) $this->bill_discount_value;

        return round(min($raw, $base), 2);
    }

    public function totalDeductions(): float
    {
        return round(
            $this->itemDiscountTotal() + $this->complimentaryTotal() + $this->billDiscountAmount()
            + $this->loyalty_amount + $this->coupon_amount,
            2
        );
    }

    public function taxableAmount(): float
    {
        return max(0, round($this->subtotal() - $this->totalDeductions(), 2));
    }

    public function cgstAmount(): float
    {
        return $this->tax_mode === 'inclusive' ? 0.0 : round($this->taxableAmount() * $this->cgst_rate, 2);
    }

    public function sgstAmount(): float
    {
        return $this->tax_mode === 'inclusive' ? 0.0 : round($this->taxableAmount() * $this->sgst_rate, 2);
    }

    public function serviceAmount(): float
    {
        return $this->service_removed ? 0.0 : round($this->taxableAmount() * $this->service_rate, 2);
    }

    public function grandTotalRaw(): float
    {
        return $this->taxableAmount() + $this->cgstAmount() + $this->sgstAmount() + $this->serviceAmount();
    }

    public function grandTotal(): int
    {
        return (int) round($this->grandTotalRaw());
    }

    public function roundOff(): float
    {
        return round($this->grandTotal() - $this->grandTotalRaw(), 2);
    }

    public function paidTotal(): float
    {
        return round($this->payments()->where('status', 'success')->sum('amount'), 2);
    }

    public function refundedTotal(): float
    {
        return round($this->refunds()->sum('amount'), 2);
    }

    public function dueAmount(): float
    {
        return max(0, round($this->grandTotal() - $this->paidTotal(), 2));
    }

    /** Recomputed from payments/refunds each time — never trust a stale stored status. */
    public function computeStatus(): string
    {
        if ($this->voided) {
            return 'voided';
        }

        $refunded = $this->refundedTotal();
        if ($refunded > 0 && $refunded >= $this->grandTotal()) {
            return 'refunded';
        }

        if ($refunded > 0) {
            return 'partially_refunded';
        }

        $paid = $this->paidTotal();
        if ($this->dueAmount() <= 0 && $paid > 0) {
            return 'paid';
        }

        return $paid > 0 ? 'partially_paid' : 'generated';
    }
}
