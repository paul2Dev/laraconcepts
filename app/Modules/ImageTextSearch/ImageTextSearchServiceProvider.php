<?php

namespace App\Modules\ImageTextSearch;

use App\Modules\ImageTextSearch\Http\Controllers\ImageTextSearchDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ImageTextSearchServiceProvider extends ServiceProvider
{
    public const SLUG = 'image-text-search';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'image-text-search');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Image Text Search',
            description: 'Types a text query and ranks a seeded set of real photos by relevance, using a hand-rolled cross-modal embedder that maps both text and images into the same vector space.',
            category: 'Search & AI',
            demoRoute: 'image-text-search.demo',
        ));

        Route::get('/concepts/image-text-search/demo', [ImageTextSearchDemoController::class, 'show'])
            ->name('image-text-search.demo');
    }
}
