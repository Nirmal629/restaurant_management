<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = ['name', 'type', 'required', 'min_select', 'max_select'];

    protected $casts = ['required' => 'boolean'];

    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class);
    }
}
