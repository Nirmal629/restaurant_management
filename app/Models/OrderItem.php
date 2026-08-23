<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'variant_label', 'qty', 'unit_price', 'modifiers', 'note',
        'allergy_note', 'kitchen_station_id', 'kot_round', 'status', 'unavailable_reason',
        'cancel_reason', 'sent_at', 'ready_at',
        'bill_status', 'bill_discount_amount', 'comp_reason', 'comp_by', 'refund_reason', 'refund_amount',
    ];

    protected $casts = [
        'modifiers' => 'array',
        'sent_at' => 'datetime',
        'ready_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function station()
    {
        return $this->belongsTo(KitchenStation::class, 'kitchen_station_id');
    }

    public function modifierTotal(): float
    {
        return collect($this->modifiers ?? [])->sum('delta');
    }

    public function grossAmount(): float
    {
        return round(($this->unit_price + $this->modifierTotal()) * $this->qty, 2);
    }

    /** What this line actually contributes to the payable subtotal — mirrors netAmount() in the Billing store. */
    public function lineTotal(): float
    {
        if (in_array($this->status, ['cancelled'], true) || $this->bill_status === 'refunded') {
            return 0.0;
        }

        if ($this->bill_status === 'complimentary') {
            return 0.0;
        }

        return round($this->grossAmount() - $this->bill_discount_amount, 2);
    }

    public function compBy()
    {
        return $this->belongsTo(Employee::class, 'comp_by');
    }
}
