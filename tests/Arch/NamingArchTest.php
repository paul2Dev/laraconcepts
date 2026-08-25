<?php

// Code Principles §1 — Naming: names reveal intent, no noise words.

arch('controllers are suffixed "Controller"')
    ->expect([
        'App\Http\Controllers',
        'App\Modules\CacheBenchmark\Http\Controllers',
        'App\Modules\CustomCasts\Http\Controllers',
        'App\Modules\SemanticSearch\Http\Controllers',
    ])
    ->classes()
    ->toHaveSuffix('Controller');

arch('service providers are suffixed "ServiceProvider"')
    ->expect([
        'App\Providers\AppServiceProvider',
        'App\Platform\ConceptRegistryServiceProvider',
        'App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider',
        'App\Modules\CustomCasts\CustomCastsServiceProvider',
        'App\Modules\SemanticSearch\SemanticSearchServiceProvider',
    ])
    ->toHaveSuffix('ServiceProvider');

arch('classes avoid the noise word "Manager"')
    ->expect('App')
    ->classes()
    ->not->toHaveSuffix('Manager');

arch('classes avoid the noise word "Helper"')
    ->expect('App')
    ->classes()
    ->not->toHaveSuffix('Helper');

arch('classes avoid the noise word "Info"')
    ->expect('App')
    ->classes()
    ->not->toHaveSuffix('Info');

arch('classes avoid the generic noise word "Data"')
    ->expect('App')
    ->classes()
    ->not->toHaveSuffix('Data');
