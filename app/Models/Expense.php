<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'code', 'date', 'category', 'description', 'vendor', 'payment_method', 'amount',
        'employee_id', 'branch_id', 'status', 'receipt_attached', 'reference', 'reject_reason', 'notes',
    ];

    protected $casts = ['date' => 'date', 'receipt_attached' => 'boolean'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function activities()
    {
        return $this->hasMany(ExpenseActivity::class)->latest('recorded_at');
    }

    public function logActivity(string $text): ExpenseActivity
    {
        return $this->activities()->create(['text' => $text, 'recorded_at' => now()]);
    }
}
