<?php

namespace App\Modules\CacheBenchmark;

use App\Modules\CacheBenchmark\Http\Controllers\CacheBenchmarkDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CacheBenchmarkServiceProvider extends ServiceProvider
{
    public const SLUG = 'cache-benchmark';

    public function boot(ConceptRegistry $registry): void
    {
        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Cache Benchmark',
            description: 'Runs the same expensive query with the cache bypassed and then cached, timing both.',
            category: 'Performance & Security',
            demoRoute: 'cache-benchmark.demo',
        ));

        Route::get('/concepts/cache-benchmark/demo', [CacheBenchmarkDemoController::class, 'show'])
            ->name('cache-benchmark.demo');
    }
}
