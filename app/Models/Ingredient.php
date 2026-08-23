<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'code', 'name', 'category', 'unit', 'current_stock', 'min_stock', 'reorder_level',
        'avg_cost', 'supplier_id', 'storage_location',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(StockLedgerEntry::class);
    }

    public function wastages()
    {
        return $this->hasMany(Wastage::class);
    }

    /** in | low | out — derived, never stored, so it can't drift from current_stock. */
    public function stockStatus(): string
    {
        if ($this->current_stock <= 0) {
            return 'out';
        }

        return $this->current_stock < $this->min_stock ? 'low' : 'in';
    }

    public function stockValue(): float
    {
        return round($this->current_stock * $this->avg_cost, 2);
    }
}
