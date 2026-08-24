<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('reports a materially lower cached timing than the uncached run when the flag is on', function () {
    Feature::activate('cache-benchmark');

    $response = $this->get(route('cache-benchmark.demo'));

    $response->assertOk();
    $response->assertJsonStructure(['uncached_ms', 'cached_ms']);

    $uncachedMs = $response->json('uncached_ms');
    $cachedMs = $response->json('cached_ms');

    expect($uncachedMs)->toBeGreaterThan(50);
    expect($cachedMs)->toBeLessThan($uncachedMs / 2);
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('cache-benchmark');

    $response = $this->get(route('cache-benchmark.demo'));

    $response->assertStatus(503);
});

it('appears on the dashboard grouped under Performance & Security', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Performance & Security', 'Cache Benchmark']);
});
