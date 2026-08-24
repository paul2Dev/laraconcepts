<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\Fixtures\Concepts\FixtureConceptServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app->register(FixtureConceptServiceProvider::class);
});

it('lists a registered concept on the dashboard grouped under its category with its flag state', function () {
    Feature::deactivate('fixture-concept');

    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Test Fixtures', 'Fixture Concept']);
    $response->assertSee('Off');
});

it('shows a concept as active once its flag is activated', function () {
    Feature::activate('fixture-concept');

    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSee('On');
});

it('toggles a concept flag and persists the flipped state', function () {
    Feature::deactivate('fixture-concept');

    $response = $this->post(route('concepts.toggle', 'fixture-concept'));

    $response->assertRedirect(route('concepts.dashboard'));
    expect(Feature::active('fixture-concept'))->toBeTrue();
    $this->assertDatabaseHas('features', [
        'name' => 'fixture-concept',
        'value' => 'true',
    ]);
});

it('toggles an active concept flag back off', function () {
    Feature::activate('fixture-concept');

    $this->post(route('concepts.toggle', 'fixture-concept'));

    expect(Feature::active('fixture-concept'))->toBeFalse();
    $this->assertDatabaseHas('features', [
        'name' => 'fixture-concept',
        'value' => 'false',
    ]);
});

it('refuses to toggle a concept that is not registered', function () {
    $response = $this->post(route('concepts.toggle', 'not-a-real-concept'));

    $response->assertNotFound();
});

it('toggles a concept flag via JSON without redirecting, for reactive callers', function () {
    Feature::deactivate('fixture-concept');

    $response = $this->postJson(route('concepts.toggle', 'fixture-concept'));

    $response->assertOk();
    $response->assertJson(['active' => true]);
    expect(Feature::active('fixture-concept'))->toBeTrue();
});
