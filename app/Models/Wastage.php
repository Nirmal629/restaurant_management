<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wastage extends Model
{
    protected $fillable = ['code', 'ingredient_id', 'qty', 'unit', 'reason', 'cost', 'employee_id', 'date', 'notes'];

    protected $casts = ['date' => 'date'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
