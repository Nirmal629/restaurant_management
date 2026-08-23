<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    protected $fillable = ['purchase_order_id', 'ingredient_id', 'current_stock_snapshot', 'qty', 'unit', 'rate', 'tax_pct'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function amount(): float
    {
        return round($this->qty * $this->rate * (1 + $this->tax_pct / 100), 2);
    }
}
