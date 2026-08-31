<?php

namespace App\Modules\LiveCollab\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'live_collab_documents';

    protected $fillable = ['content'];
}
