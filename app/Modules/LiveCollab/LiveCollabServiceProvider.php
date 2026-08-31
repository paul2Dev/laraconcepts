<?php

namespace App\Modules\LiveCollab;

use App\Modules\LiveCollab\Http\Controllers\LiveCollabDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LiveCollabServiceProvider extends ServiceProvider
{
    public const SLUG = 'live-collab';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'live-collab');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Live Collab',
            description: 'Edits a shared demo document in a plain textarea, broadcasting each change over a Reverb channel scoped to that document so every open session sees it live.',
            category: 'Real-time',
            demoRoute: 'live-collab.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/live-collab/demo', [LiveCollabDemoController::class, 'show'])
                ->name('live-collab.demo');

            Route::post('/concepts/live-collab/demo/edit', [LiveCollabDemoController::class, 'edit'])
                ->name('live-collab.edit');
        });
    }
}
