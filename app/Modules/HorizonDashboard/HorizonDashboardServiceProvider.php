<?php

namespace App\Modules\HorizonDashboard;

use App\Modules\HorizonDashboard\Http\Controllers\HorizonDashboardController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HorizonDashboardServiceProvider extends ServiceProvider
{
    public const SLUG = 'horizon-dashboard';

    public function boot(ConceptRegistry $registry): void
    {
        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Horizon Dashboard',
            description: 'Gates access to Laravel Horizon\'s own dashboard for monitoring the Redis-backed queue behind a Pennant flag.',
            category: 'DevOps / Observability',
            demoRoute: 'horizon-dashboard.demo',
        ));

        Route::get('/concepts/horizon-dashboard/demo', [HorizonDashboardController::class, 'show'])
            ->name('horizon-dashboard.demo');
    }
}
