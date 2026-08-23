<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $fillable = ['name', 'price'];

    public function items()
    {
        return $this->hasMany(ComboItem::class);
    }
}
