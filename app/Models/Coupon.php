<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'value', 'min_bill_amount', 'max_discount_amount',
        'starts_at', 'expires_at', 'usage_limit', 'per_customer_limit', 'walkin_allowed', 'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_bill_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'walkin_allowed' => 'boolean',
        'active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function normalizedCode(): string
    {
        return strtoupper(trim($this->code));
    }

    public function discountFor(float $amount): float
    {
        $discount = $this->type === 'percent'
            ? $amount * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount_amount !== null) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(min($discount, $amount), 2);
    }

    public function validateFor(Invoice $invoice): ?string
    {
        $today = now()->toDateString();
        $customerId = $invoice->order?->customer_id;

        if (! $this->active) return 'Coupon is inactive.';
        if ($this->starts_at && $this->starts_at->toDateString() > $today) return 'Coupon is not active yet.';
        if ($this->expires_at && $this->expires_at->toDateString() < $today) return 'Coupon has expired.';
        if (! $this->walkin_allowed && ! $customerId) return 'Coupon requires an attached customer.';
        if ($invoice->couponBaseAmount() < (float) $this->min_bill_amount) return 'Minimum bill amount not reached.';
        if ($this->usage_limit && $this->redemptions()->count() >= $this->usage_limit) return 'Coupon usage limit reached.';
        if ($customerId && $this->per_customer_limit && $this->redemptions()->where('customer_id', $customerId)->count() >= $this->per_customer_limit) {
            return 'Customer has already used this coupon.';
        }

        return null;
    }
}
