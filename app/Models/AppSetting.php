<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['section', 'values'];

    protected $casts = [
        'values' => 'array',
    ];
}
