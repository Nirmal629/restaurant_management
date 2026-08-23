<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseActivity extends Model
{
    public $timestamps = false;

    protected $fillable = ['expense_id', 'text', 'recorded_at'];

    protected $casts = ['recorded_at' => 'datetime'];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
