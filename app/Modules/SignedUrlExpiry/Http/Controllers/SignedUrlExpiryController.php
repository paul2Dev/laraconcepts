<?php

namespace App\Modules\SignedUrlExpiry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SignedUrlExpiry\SignedUrlExpiryServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Laravel\Pennant\Feature;

class SignedUrlExpiryController extends Controller
{
    private const EXPIRY_SECONDS = 45;

    public function show(): Response
    {
        if (! Feature::active(SignedUrlExpiryServiceProvider::SLUG)) {
            return response()->view('signed-url-expiry::demo', ['active' => false], 503);
        }

        $expiresAt = now()->addSeconds(self::EXPIRY_SECONDS);

        return response()->view('signed-url-expiry::demo', [
            'active' => true,
            'signedUrl' => URL::temporarySignedRoute('signed-url-expiry.download', $expiresAt),
            'expiresAt' => $expiresAt,
        ]);
    }

    public function download(Request $request): Response|JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'This link has expired or was tampered with.'], 403);
        }

        return response(
            "This file was downloaded through a signed URL that was still valid.\n",
            200,
            ['Content-Disposition' => 'attachment; filename="signed-url-expiry-demo.txt"'],
        );
    }
}
