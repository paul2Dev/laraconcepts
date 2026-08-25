<?php

namespace App\Modules\RateLimitDemo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RateLimitDemo\RateLimitDemoServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;

class RateLimitDemoController extends Controller
{
    private const CACHE_KEY = 'rate-limit-demo:request-count';

    public function show(): JsonResponse
    {
        if (! Feature::active(RateLimitDemoServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        Cache::add(self::CACHE_KEY, 0);

        return response()->json(['request_count' => Cache::increment(self::CACHE_KEY)]);
    }
}
