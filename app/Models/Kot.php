<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kot extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'order_id', 'round', 'printer', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Items dispatched in this specific KOT round. */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id')->where('kot_round', $this->round);
    }
}
