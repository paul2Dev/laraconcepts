<?php

namespace App\Modules\ImageTextSearch\Models;

use App\Modules\ImageTextSearch\Casts\VectorCast;
use Illuminate\Database\Eloquent\Model;

class SeededImage extends Model
{
    protected $table = 'image_text_search_images';

    protected $fillable = ['label', 'image', 'embedding'];

    protected $casts = [
        'embedding' => VectorCast::class,
    ];
}
