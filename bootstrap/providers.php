<?php

use App\Modules\AuditLog\AuditLogServiceProvider;
use App\Modules\CacheBenchmark\CacheBenchmarkServiceProvider;
use App\Modules\CustomCasts\CustomCastsServiceProvider;
use App\Modules\HorizonDashboard\HorizonDashboardServiceProvider;
use App\Modules\ImageSimilarity\ImageSimilarityServiceProvider;
use App\Modules\ImageTextSearch\ImageTextSearchServiceProvider;
use App\Modules\JobProgress\JobProgressServiceProvider;
use App\Modules\LiveCollab\LiveCollabServiceProvider;
use App\Modules\LiveNotifications\LiveNotificationsServiceProvider;
use App\Modules\Presence\PresenceServiceProvider;
use App\Modules\RateLimitDemo\RateLimitDemoServiceProvider;
use App\Modules\SemanticSearch\SemanticSearchServiceProvider;
use App\Modules\SignedUrlExpiry\SignedUrlExpiryServiceProvider;
use App\Platform\ConceptRegistryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    ConceptRegistryServiceProvider::class,
    HorizonServiceProvider::class,
    AuditLogServiceProvider::class,
    CacheBenchmarkServiceProvider::class,
    CustomCastsServiceProvider::class,
    HorizonDashboardServiceProvider::class,
    ImageSimilarityServiceProvider::class,
    ImageTextSearchServiceProvider::class,
    JobProgressServiceProvider::class,
    LiveCollabServiceProvider::class,
    LiveNotificationsServiceProvider::class,
    PresenceServiceProvider::class,
    RateLimitDemoServiceProvider::class,
    SemanticSearchServiceProvider::class,
    SignedUrlExpiryServiceProvider::class,
];
