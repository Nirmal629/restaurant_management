<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'birthday', 'anniversary', 'address', 'gstin', 'business_name',
        'notes', 'allergies', 'tags', 'is_vip', 'loyalty_points', 'joined_date',
    ];

    protected $casts = [
        'birthday' => 'date',
        'anniversary' => 'date',
        'joined_date' => 'date',
        'tags' => 'array',
        'is_vip' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function visits(): int
    {
        return $this->orders()->count();
    }
}
