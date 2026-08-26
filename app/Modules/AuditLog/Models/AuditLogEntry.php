<?php

namespace App\Modules\AuditLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLogEntry extends Model
{
    protected $table = 'audit_log_entries';

    protected $fillable = ['actor', 'action', 'subject'];

    /**
     * Orders by id rather than created_at: two writes in the same second would
     * otherwise tie, leaving "newest first" unspecified.
     */
    public function scopeNewestFirst(Builder $query): void
    {
        $query->latest('id');
    }
}
