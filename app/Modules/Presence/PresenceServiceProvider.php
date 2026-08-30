<?php

namespace App\Modules\Presence;

use App\Modules\Presence\Http\Controllers\PresenceDemoController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class PresenceServiceProvider extends ServiceProvider
{
    public const SLUG = 'presence';

    public const CHANNEL = 'demo';

    public const GUARD = 'presence-guest';

    public const SESSION_GUEST = 'presence.guest';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'presence');

        // Presence channels require an authenticated guard user, but this
        // platform has no login system. `Auth::viaRequest` registers a guard
        // that treats "has a guest identity in the session" as authenticated,
        // so the demo can join a real presence channel without a User model.
        config(['auth.guards.'.self::GUARD => ['driver' => self::GUARD]]);

        Auth::viaRequest(self::GUARD, function (Request $request) {
            $guest = $request->session()->get(self::SESSION_GUEST);

            return $guest ? new GenericUser($guest) : null;
        });

        Broadcast::channel(self::CHANNEL, function (?GenericUser $user) {
            if (! $user || ! Feature::active(self::SLUG)) {
                return false;
            }

            return ['id' => $user->getAuthIdentifier(), 'name' => $user->name];
        }, ['guards' => [self::GUARD]]);

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Presence',
            description: 'Joins a Reverb presence channel showing a live roster of who is online, with a typing indicator whispered directly between clients.',
            category: 'Real-time',
            demoRoute: 'presence.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/presence/demo', [PresenceDemoController::class, 'show'])
                ->name('presence.demo');
        });
    }
}
