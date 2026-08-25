<?php

use App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider;
use App\Modules\CustomCasts\CustomCastsServiceProvider;
use App\Modules\HorizonDashboard\HorizonDashboardServiceProvider;
use App\Modules\JobProgress\JobProgressServiceProvider;
use App\Modules\RateLimitDemo\RateLimitDemoServiceProvider;
use App\Modules\SemanticSearch\SemanticSearchServiceProvider;
use App\Platform\ConceptRegistryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    ConceptRegistryServiceProvider::class,
    HorizonServiceProvider::class,
    CacheBenchmarkServiceProvider::class,
    CustomCastsServiceProvider::class,
    HorizonDashboardServiceProvider::class,
    JobProgressServiceProvider::class,
    RateLimitDemoServiceProvider::class,
    SemanticSearchServiceProvider::class,
];
