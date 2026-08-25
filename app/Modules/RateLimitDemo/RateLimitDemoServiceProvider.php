<?php

namespace App\Modules\RateLimitDemo;

use App\Modules\RateLimitDemo\Http\Controllers\RateLimitDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RateLimitDemoServiceProvider extends ServiceProvider
{
    public const SLUG = 'rate-limit-demo';

    public function boot(ConceptRegistry $registry): void
    {
        RateLimiter::for(self::SLUG, fn (Request $request) => Limit::perSecond(5, 10)->by($request->ip()));

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Rate Limit Demo',
            description: 'Spams a demo route behind a dedicated 5-requests-per-10-seconds limiter, returning a live request count until it throttles with a 429.',
            category: 'Performance & Security',
            demoRoute: 'rate-limit-demo.demo',
        ));

        Route::get('/concepts/rate-limit-demo/demo', [RateLimitDemoController::class, 'show'])
            ->middleware('throttle:'.self::SLUG)
            ->name('rate-limit-demo.demo');
    }
}
