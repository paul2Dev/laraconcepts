<?php

namespace App\Modules\JobProgress\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\JobProgress\JobProgressServiceProvider;
use App\Modules\JobProgress\Jobs\ProcessUploadJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

class JobProgressDemoController extends Controller
{
    public function show(): Response
    {
        $active = $this->isActive();

        return response()
            ->view('job-progress::demo', ['active' => $active])
            ->setStatusCode($active ? 200 : 503);
    }

    public function upload(Request $request): JsonResponse
    {
        if (! $this->isActive()) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $uploadId = (string) Str::uuid();
        $path = $request->file('file')->store('job-progress-uploads', 'local');

        ProcessUploadJob::dispatch($uploadId, $path);

        return response()->json([
            'upload_id' => $uploadId,
            'channel' => "job-progress.{$uploadId}",
        ]);
    }

    private function isActive(): bool
    {
        return Feature::active(JobProgressServiceProvider::SLUG);
    }
}
