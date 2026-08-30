<?php

namespace App\Modules\Presence\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Presence\PresenceServiceProvider;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

class PresenceDemoController extends Controller
{
    /** @var list<string> */
    private const GUEST_NAMES = [
        'Curious Otter', 'Quiet Falcon', 'Bright Fox', 'Steady Heron',
        'Swift Lynx', 'Calm Raven', 'Bold Badger', 'Sharp Wren',
    ];

    public function show(): Response
    {
        if (! Feature::active(PresenceServiceProvider::SLUG)) {
            return response()->view('presence::demo', ['active' => false])->setStatusCode(503);
        }

        if (! session()->has(PresenceServiceProvider::SESSION_GUEST)) {
            session()->put(PresenceServiceProvider::SESSION_GUEST, [
                'id' => (string) Str::uuid(),
                'name' => Arr::random(self::GUEST_NAMES),
            ]);
        }

        return response()->view('presence::demo', [
            'active' => true,
            'channel' => PresenceServiceProvider::CHANNEL,
            'guestName' => session(PresenceServiceProvider::SESSION_GUEST)['name'],
        ]);
    }
}
