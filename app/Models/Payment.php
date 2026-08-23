<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'method', 'amount', 'tendered', 'reference', 'status', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function changeDue(): float
    {
        return $this->tendered ? max(0, round($this->tendered - $this->amount, 2)) : 0.0;
    }
}
