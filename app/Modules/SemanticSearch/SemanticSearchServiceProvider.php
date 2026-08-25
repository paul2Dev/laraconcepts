<?php

namespace App\Modules\SemanticSearch;

use App\Modules\SemanticSearch\Http\Controllers\SemanticSearchDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SemanticSearchServiceProvider extends ServiceProvider
{
    public const SLUG = 'semantic-search';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Semantic Search',
            description: 'Searches a seeded product catalog with a toggle between plain keyword LIKE matching and embedding-based semantic ranking.',
            category: 'Search & AI',
            demoRoute: 'semantic-search.demo',
        ));

        Route::get('/concepts/semantic-search/demo', [SemanticSearchDemoController::class, 'show'])
            ->name('semantic-search.demo');
    }
}
