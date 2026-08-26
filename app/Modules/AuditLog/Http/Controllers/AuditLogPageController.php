<?php

namespace App\Modules\AuditLog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AuditLog\AuditLogServiceProvider;
use App\Modules\AuditLog\Models\AuditLogEntry;
use App\Modules\AuditLog\Models\Note;
use Illuminate\Http\Response;
use Laravel\Pennant\Feature;

class AuditLogPageController extends Controller
{
    public function show(): Response
    {
        $active = Feature::active(AuditLogServiceProvider::SLUG);

        return response()
            ->view('audit-log::demo', [
                'active' => $active,
                'notes' => $active ? Note::query()->latest('id')->get() : collect(),
                'entries' => $active ? AuditLogEntry::newestFirst()->get() : collect(),
            ])
            ->setStatusCode($active ? 200 : 503);
    }
}
