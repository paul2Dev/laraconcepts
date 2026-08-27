<?php

namespace App\Providers;

use App\Modules\HorizonDashboard\HorizonDashboardServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Pennant\Feature;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        // This app has no user auth, so the usual email-allowlist pattern doesn't
        // apply — access to /horizon itself is tied to the same Pennant flag the
        // horizon-dashboard demo route already gates, so toggling it controls both.
        Gate::define('viewHorizon', function ($user = null) {
            return Feature::active(HorizonDashboardServiceProvider::SLUG);
        });
    }
}
