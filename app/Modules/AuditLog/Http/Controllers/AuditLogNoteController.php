<?php

namespace App\Modules\AuditLog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AuditLog\AuditLogRecorder;
use App\Modules\AuditLog\AuditLogServiceProvider;
use App\Modules\AuditLog\Models\AuditLogEntry;
use App\Modules\AuditLog\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;

class AuditLogNoteController extends Controller
{
    public function __construct(private readonly AuditLogRecorder $recorder) {}

    public function store(Request $request): JsonResponse
    {
        if (! Feature::active(AuditLogServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $note = Note::query()->create($request->validate(['title' => ['required', 'string', 'max:255']]));

        $this->recorder->record($request, 'created', $note);

        return $this->state();
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        if (! Feature::active(AuditLogServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $note->update($request->validate(['title' => ['required', 'string', 'max:255']]));

        $this->recorder->record($request, 'updated', $note);

        return $this->state();
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        if (! Feature::active(AuditLogServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $subject = $this->recorder->describe($note);
        $note->delete();

        $this->recorder->record($request, 'deleted', $subject);

        return $this->state();
    }

    private function state(): JsonResponse
    {
        return response()->json([
            'notes' => Note::query()->latest('id')->get(),
            'entries' => AuditLogEntry::newestFirst()->get(),
        ]);
    }
}
