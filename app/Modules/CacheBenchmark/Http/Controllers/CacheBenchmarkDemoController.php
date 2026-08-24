<?php

namespace App\Modules\CacheBenchmark\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Laravel\Pennant\Feature;

class CacheBenchmarkDemoController extends Controller
{
    private const CACHE_KEY = 'cache-benchmark:expensive-result';

    public function show(): JsonResponse
    {
        if (! Feature::active(CacheBenchmarkServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        Cache::forget(self::CACHE_KEY);

        $uncachedMs = $this->timeCacheRemember();
        $cachedMs = $this->timeCacheRemember();

        return response()->json([
            'uncached_ms' => round($uncachedMs, 2),
            'cached_ms' => round($cachedMs, 2),
        ]);
    }

    private function timeCacheRemember(): float
    {
        $start = microtime(true);

        Cache::remember(self::CACHE_KEY, now()->addMinutes(5), $this->runExpensiveQuery(...));

        return (microtime(true) - $start) * 1000;
    }

    private function runExpensiveQuery(): int
    {
        usleep(100_000);

        return random_int(1, 1000);
    }
}
