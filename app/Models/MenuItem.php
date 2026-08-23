<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'sku', 'name', 'short_name', 'menu_category_id', 'subcategory', 'description', 'image_path',
        'diet_type', 'base_price', 'tax_profile', 'prep_time_minutes', 'kitchen_station_id',
        'availability', 'featured', 'popular', 'stock_tracked', 'online_visible', 'display_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'featured' => 'boolean',
        'popular' => 'boolean',
        'stock_tracked' => 'boolean',
        'online_visible' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function station()
    {
        return $this->belongsTo(KitchenStation::class, 'kitchen_station_id');
    }

    public function variants()
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }
}
