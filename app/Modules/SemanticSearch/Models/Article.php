<?php

namespace App\Modules\SemanticSearch\Models;

use App\Modules\SemanticSearch\Casts\VectorCast;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'semantic_search_articles';

    protected $fillable = ['title', 'body', 'embedding'];

    protected $casts = [
        'embedding' => VectorCast::class,
    ];
}
