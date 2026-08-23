<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    public $timestamps = false;

    protected $fillable = ['coupon_id', 'invoice_id', 'customer_id', 'amount', 'redeemed_at'];

    protected $casts = ['redeemed_at' => 'datetime'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
