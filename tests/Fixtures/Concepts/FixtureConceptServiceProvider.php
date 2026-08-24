<?php

namespace Tests\Fixtures\Concepts;

use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class FixtureConceptServiceProvider extends ServiceProvider
{
    public function boot(ConceptRegistry $registry): void
    {
        $registry->register(new ConceptRegistration(
            slug: 'fixture-concept',
            name: 'Fixture Concept',
            description: 'A test-only concept used to prove the registry and dashboard wiring.',
            category: 'Test Fixtures',
            demoRoute: 'fixture-concept.demo',
        ));

        Route::get('/fixture-concept/demo', fn () => Feature::active('fixture-concept')
            ? response('fixture concept demo')
            : response('unavailable', 503)
        )->name('fixture-concept.demo');
    }
}
