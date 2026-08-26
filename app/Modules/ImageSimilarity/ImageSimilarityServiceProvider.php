<?php

namespace App\Modules\ImageSimilarity;

use App\Modules\ImageSimilarity\Http\Controllers\ImageSimilarityDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ImageSimilarityServiceProvider extends ServiceProvider
{
    public const SLUG = 'image-similarity';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'image-similarity');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Image Similarity',
            description: 'Uploads an image and ranks a seeded set of real photos by visual similarity, using a hand-rolled grid-color embedding and cosine distance.',
            category: 'Search & AI',
            demoRoute: 'image-similarity.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/image-similarity/demo', [ImageSimilarityDemoController::class, 'show'])
                ->name('image-similarity.demo');

            Route::post('/concepts/image-similarity/demo', [ImageSimilarityDemoController::class, 'upload'])
                ->name('image-similarity.upload');
        });
    }
}
