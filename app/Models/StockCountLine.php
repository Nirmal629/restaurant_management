<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCountLine extends Model
{
    protected $fillable = ['stock_count_id', 'ingredient_id', 'system_qty', 'physical_qty', 'reason'];

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function variance(): float
    {
        return $this->physical_qty - $this->system_qty;
    }

    public function varianceValue(): float
    {
        return round($this->variance() * ($this->ingredient?->avg_cost ?? 0), 2);
    }
}
