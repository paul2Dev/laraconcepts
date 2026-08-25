<?php

use App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider;
use App\Modules\CustomCasts\CustomCastsServiceProvider;
use App\Modules\JobProgress\JobProgressServiceProvider;
use App\Modules\RateLimitDemo\RateLimitDemoServiceProvider;
use App\Modules\SemanticSearch\SemanticSearchServiceProvider;
use App\Platform\ConceptRegistryServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    ConceptRegistryServiceProvider::class,
    CacheBenchmarkServiceProvider::class,
    CustomCastsServiceProvider::class,
    JobProgressServiceProvider::class,
    RateLimitDemoServiceProvider::class,
    SemanticSearchServiceProvider::class,
];
