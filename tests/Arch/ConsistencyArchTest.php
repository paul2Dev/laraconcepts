<?php

// Code Principles §7 — Consistency: one way to do a recurring thing across the codebase.

arch('service providers extend the framework base class')
    ->expect([
        'App\Providers\AppServiceProvider',
        'App\Platform\ConceptRegistryServiceProvider',
        'App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider',
        'App\Modules\CustomCasts\CustomCastsServiceProvider',
        'App\Modules\SemanticSearch\SemanticSearchServiceProvider',
    ])
    ->toExtend('Illuminate\Support\ServiceProvider');

arch('controllers extend the shared application controller')
    ->expect([
        'App\Http\Controllers',
        'App\Modules\CacheBenchmark\Http\Controllers',
        'App\Modules\CustomCasts\Http\Controllers',
        'App\Modules\SemanticSearch\Http\Controllers',
    ])
    ->classes()
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('Eloquent models extend the framework base model')
    ->expect([
        'App\Models',
        'App\Modules\CustomCasts\Models',
        'App\Modules\SemanticSearch\Models',
    ])
    ->classes()
    ->toExtend('Illuminate\Database\Eloquent\Model');
