<?php

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\Broadcasting\ReverbSmokeTestEvent;

it('broadcasts the smoke-test event on its expected channel', function () {
    Event::fake([ReverbSmokeTestEvent::class]);

    event(new ReverbSmokeTestEvent('hello from reverb'));

    Event::assertDispatched(
        ReverbSmokeTestEvent::class,
        fn (ReverbSmokeTestEvent $event) => $event->broadcastOn()[0]->name === 'reverb-smoke-test'
    );
});
