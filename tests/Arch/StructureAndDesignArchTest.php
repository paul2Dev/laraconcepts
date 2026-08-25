<?php

// Code Principles §3 — Structure & design: orthogonality, SRP, data vs. objects, Demeter.

arch('the cache-benchmark module stays orthogonal to the custom-casts module')
    ->expect('App\Modules\CacheBenchmark')
    ->not->toUse('App\Modules\CustomCasts');

arch('the custom-casts module stays orthogonal to the cache-benchmark module')
    ->expect('App\Modules\CustomCasts')
    ->not->toUse('App\Modules\CacheBenchmark');

arch('the shared platform kernel does not depend on any specific concept module')
    ->expect('App\Platform')
    ->not->toUse('App\Modules');

arch('value objects are immutable data carriers: final and readonly')
    ->expect('App\Modules\CustomCasts\ValueObjects')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('concept registrations are immutable data carriers: final and readonly')
    ->expect('App\Platform\ConceptRegistration')
    ->toBeFinal()
    ->toBeReadonly();

arch('the concept registry is a final, encapsulated object rather than a bag of public state')
    ->expect('App\Platform\ConceptRegistry')
    ->toBeFinal();

arch('models talk to their immediate collaborators only, not through HTTP controllers')
    ->expect('App\Modules\CustomCasts\Models')
    ->not->toUse('App\Modules\CustomCasts\Http\Controllers');

arch('classes stay small and single-purpose')
    ->expect('App')
    ->classes()
    ->toHaveLineCountLessThan(80);
