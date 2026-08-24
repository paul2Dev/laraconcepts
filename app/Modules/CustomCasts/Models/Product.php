<?php

namespace App\Modules\CustomCasts\Models;

use App\Modules\CustomCasts\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'custom_casts_products';

    protected $fillable = ['name', 'price'];

    protected $casts = [
        'price' => MoneyCast::class,
    ];
}
