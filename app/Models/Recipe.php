<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['menu_item_id'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function lines()
    {
        return $this->hasMany(RecipeLine::class);
    }

    public function estimatedCost(): float
    {
        return round($this->lines->sum(fn (RecipeLine $line) => $line->qty * ($line->ingredient?->avg_cost ?? 0)), 2);
    }

    public function foodCostPercent(): float
    {
        $price = (float) ($this->menuItem?->base_price ?? 0);

        return $price > 0 ? round(($this->estimatedCost() / $price) * 100) : 0;
    }

    public function estimatedMargin(): float
    {
        return round((float) ($this->menuItem?->base_price ?? 0) - $this->estimatedCost(), 2);
    }
}
