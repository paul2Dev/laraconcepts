<?php

namespace App\Modules\JobProgress;

use App\Modules\JobProgress\Http\Controllers\JobProgressDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class JobProgressServiceProvider extends ServiceProvider
{
    public const SLUG = 'job-progress';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'job-progress');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Job Progress',
            description: 'Uploads a file to a queued job that counts it in byte chunks, broadcasting live percentage-complete updates over a Reverb channel scoped to that upload.',
            category: 'Real-time',
            demoRoute: 'job-progress.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/job-progress/demo', [JobProgressDemoController::class, 'show'])
                ->name('job-progress.demo');

            Route::post('/concepts/job-progress/demo', [JobProgressDemoController::class, 'upload'])
                ->name('job-progress.upload');
        });
    }
}
