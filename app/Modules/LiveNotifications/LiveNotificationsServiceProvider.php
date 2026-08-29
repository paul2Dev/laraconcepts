<?php

namespace App\Modules\LiveNotifications;

use App\Modules\LiveNotifications\Http\Controllers\LiveNotificationsDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LiveNotificationsServiceProvider extends ServiceProvider
{
    public const SLUG = 'live-notifications';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'live-notifications');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Live Notifications',
            description: 'Fires a Laravel notification broadcast over its own Reverb channel, rendered live in a bell icon + feed with an unread count, no page refresh.',
            category: 'Real-time',
            demoRoute: 'live-notifications.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/live-notifications/demo', [LiveNotificationsDemoController::class, 'show'])
                ->name('live-notifications.demo');

            Route::post('/concepts/live-notifications/demo/notify', [LiveNotificationsDemoController::class, 'notify'])
                ->name('live-notifications.notify');
        });
    }
}
