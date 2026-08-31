<?php

namespace App\Modules\LiveCollab\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LiveCollab\Events\DocumentEdited;
use App\Modules\LiveCollab\LiveCollabServiceProvider;
use App\Modules\LiveCollab\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Pennant\Feature;

class LiveCollabDemoController extends Controller
{
    /** The demo has exactly one shared document, identified by a fixed ID. */
    private const DEMO_DOCUMENT_ID = 1;

    public function show(): Response
    {
        $active = $this->isActive();
        $document = $active ? $this->document() : null;

        return response()
            ->view('live-collab::demo', [
                'active' => $active,
                'document' => $document,
                'channel' => $document ? DocumentEdited::channelName($document->id) : null,
            ])
            ->setStatusCode($active ? 200 : 503);
    }

    public function edit(Request $request): JsonResponse
    {
        if (! $this->isActive()) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:10000'],
            'client_id' => ['required', 'string'],
        ]);

        Document::updateOrCreate(['id' => self::DEMO_DOCUMENT_ID], ['content' => $validated['content']]);

        DocumentEdited::dispatch(self::DEMO_DOCUMENT_ID, $validated['content'], $validated['client_id']);

        return response()->json(['status' => 'ok']);
    }

    private function document(): Document
    {
        return Document::firstOrCreate(['id' => self::DEMO_DOCUMENT_ID], ['content' => '']);
    }

    private function isActive(): bool
    {
        return Feature::active(LiveCollabServiceProvider::SLUG);
    }
}
