<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = ['floor_id', 'code', 'name', 'seats', 'shape', 'status', 'merged_with_table_id', 'is_merge_primary'];

    protected $casts = ['is_merge_primary' => 'boolean'];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function mergedWith()
    {
        return $this->belongsTo(RestaurantTable::class, 'merged_with_table_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
