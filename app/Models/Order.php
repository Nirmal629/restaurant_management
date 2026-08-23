<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['code', 'type', 'table_id', 'customer_id', 'waiter_id', 'guests', 'token', 'status', 'started_at'];

    protected $casts = ['started_at' => 'datetime'];

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function waiter()
    {
        return $this->belongsTo(Employee::class, 'waiter_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function kots()
    {
        return $this->hasMany(Kot::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /** Non-cancelled, non-refunded lines — the set the whole billing chain operates on. */
    public function billableItems()
    {
        return $this->items->reject(fn (OrderItem $i) => $i->status === 'cancelled' || $i->bill_status === 'refunded');
    }

    /** Gross, pre-discount total of billable lines — item discounts/complimentary are separate deduction lines, not baked in here. */
    public function subtotal(): float
    {
        return round($this->billableItems()->sum(fn (OrderItem $i) => $i->grossAmount()), 2);
    }

    public function itemDiscountTotal(): float
    {
        return round($this->billableItems()->where('bill_status', 'discounted')->sum('bill_discount_amount'), 2);
    }

    public function complimentaryTotal(): float
    {
        return round($this->billableItems()->where('bill_status', 'complimentary')->sum(fn (OrderItem $i) => $i->grossAmount()), 2);
    }
}
