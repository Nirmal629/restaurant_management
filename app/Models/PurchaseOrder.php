<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'code', 'supplier_id', 'date', 'expected_delivery', 'reference', 'notes', 'status',
        'discount', 'other_charges', 'created_by', 'approved_by',
    ];

    protected $casts = ['date' => 'date', 'expected_delivery' => 'date'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function subtotal(): float
    {
        return round($this->lines->sum(fn (PurchaseOrderLine $l) => $l->qty * $l->rate), 2);
    }

    public function taxTotal(): float
    {
        return round($this->lines->sum(fn (PurchaseOrderLine $l) => $l->qty * $l->rate * ($l->tax_pct / 100)), 2);
    }

    public function grandTotal(): float
    {
        return round($this->subtotal() + $this->taxTotal() - $this->discount + $this->other_charges, 2);
    }
}
