<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('allows requests under the limit and throttles the one that exceeds it when the flag is on', function () {
    Feature::activate('rate-limit-demo');

    for ($i = 1; $i <= 5; $i++) {
        $this->get(route('rate-limit-demo.demo'))->assertOk();
    }

    $response = $this->get(route('rate-limit-demo.demo'));

    $response->assertStatus(429);
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('rate-limit-demo');

    $response = $this->get(route('rate-limit-demo.demo'));

    $response->assertStatus(503);
});

it('appears on the dashboard grouped under Performance & Security', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Performance & Security', 'Rate Limit Demo']);
});
