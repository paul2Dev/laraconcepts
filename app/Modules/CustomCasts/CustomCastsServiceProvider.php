<?php

namespace App\Modules\CustomCasts;

use App\Modules\CustomCasts\Http\Controllers\CustomCastsDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CustomCastsServiceProvider extends ServiceProvider
{
    public const SLUG = 'custom-casts';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Custom Casts & value objects',
            description: 'Round-trips a Money value object through a custom Eloquent cast backed by two database columns.',
            category: 'Search & Data',
            demoRoute: 'custom-casts.demo',
        ));

        Route::get('/concepts/custom-casts/demo', [CustomCastsDemoController::class, 'show'])
            ->name('custom-casts.demo');
    }
}
