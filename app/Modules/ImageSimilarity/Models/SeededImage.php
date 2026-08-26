<?php

namespace App\Modules\ImageSimilarity\Models;

use App\Modules\ImageSimilarity\Casts\VectorCast;
use Illuminate\Database\Eloquent\Model;

class SeededImage extends Model
{
    protected $table = 'image_similarity_images';

    protected $fillable = ['label', 'image', 'embedding'];

    protected $casts = [
        'embedding' => VectorCast::class,
    ];
}
