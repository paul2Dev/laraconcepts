<?php

namespace App\Modules\AuditLog;

use App\Modules\AuditLog\Models\AuditLogEntry;
use App\Modules\AuditLog\Models\Note;
use Illuminate\Http\Request;

class AuditLogRecorder
{
    public function record(Request $request, string $action, Note|string $subject): void
    {
        AuditLogEntry::query()->create([
            'actor' => $request->ip(),
            'action' => $action,
            'subject' => is_string($subject) ? $subject : $this->describe($subject),
        ]);
    }

    public function describe(Note $note): string
    {
        return "Note #{$note->id}: {$note->title}";
    }
}
