<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationActivity extends Model
{
    public $timestamps = false;

    protected $fillable = ['reservation_id', 'text', 'recorded_at'];

    protected $casts = ['recorded_at' => 'datetime'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
