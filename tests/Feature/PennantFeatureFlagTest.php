<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('activates a feature flag and persists the state in the features table', function () {
    Feature::define('poc-platform-smoke-test', fn () => false);

    Feature::activate('poc-platform-smoke-test');

    expect(Feature::active('poc-platform-smoke-test'))->toBeTrue();
    $this->assertDatabaseHas('features', [
        'name' => 'poc-platform-smoke-test',
        'value' => 'true',
    ]);
});

it('deactivates a feature flag and persists the state in the features table', function () {
    Feature::define('poc-platform-smoke-test', fn () => false);
    Feature::activate('poc-platform-smoke-test');

    Feature::deactivate('poc-platform-smoke-test');

    expect(Feature::active('poc-platform-smoke-test'))->toBeFalse();
    $this->assertDatabaseHas('features', [
        'name' => 'poc-platform-smoke-test',
        'value' => 'false',
    ]);
});
