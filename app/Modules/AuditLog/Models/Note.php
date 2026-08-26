<?php

namespace App\Modules\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'audit_log_notes';

    protected $fillable = ['title'];
}
