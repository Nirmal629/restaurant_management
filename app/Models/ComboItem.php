<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    protected $fillable = ['combo_id', 'menu_item_id', 'qty'];

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
