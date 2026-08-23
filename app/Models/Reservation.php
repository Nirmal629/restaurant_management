<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'code', 'customer_id', 'customer_name', 'phone', 'email', 'date', 'time', 'guests',
        'floor_id', 'table_id', 'status', 'occasion', 'special_request', 'source', 'deposit', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(ReservationActivity::class)->latest('recorded_at');
    }

    public function logActivity(string $text): ReservationActivity
    {
        return $this->activities()->create(['text' => $text, 'recorded_at' => now()]);
    }
}
