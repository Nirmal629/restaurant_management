<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedgerEntry extends Model
{
    protected $fillable = ['ingredient_id', 'type', 'reference', 'previous_qty', 'change_qty', 'new_qty', 'employee_id', 'recorded_at'];

    protected $casts = ['recorded_at' => 'datetime'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
