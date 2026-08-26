<?php

namespace App\Modules\AuditLog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AuditLog\AuditLogServiceProvider;
use App\Modules\AuditLog\Models\AuditLogEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Pennant\Feature;

class AuditLogFeedController extends Controller
{
    public function show(Request $request): Response|JsonResponse
    {
        if (! Feature::active(AuditLogServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $entries = AuditLogEntry::newestFirst()->get();

        if ($request->wantsJson()) {
            return response()->json(['entries' => $entries]);
        }

        return response()->view('audit-log::feed', ['entries' => $entries]);
    }
}
