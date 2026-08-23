<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $fillable = ['name', 'display_order'];

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }
}
