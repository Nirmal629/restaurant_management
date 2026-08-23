<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemVariant extends Model
{
    protected $fillable = ['menu_item_id', 'label', 'price'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
