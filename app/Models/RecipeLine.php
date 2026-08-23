<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeLine extends Model
{
    protected $fillable = ['recipe_id', 'ingredient_id', 'qty', 'unit'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
