<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('round-trips a Money value object through the custom cast when the flag is on', function () {
    Feature::activate('custom-casts');

    $response = $this->get(route('custom-casts.demo'));

    $response->assertOk();
    $response->assertJson([
        'name' => 'Demo Widget',
        'price' => [
            'formatted' => 'USD 19.99',
            'amount_in_cents' => 1999,
            'currency' => 'USD',
        ],
    ]);
    $this->assertDatabaseHas('custom_casts_products', [
        'name' => 'Demo Widget',
        'price_amount' => 1999,
        'price_currency' => 'USD',
    ]);
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('custom-casts');

    $response = $this->get(route('custom-casts.demo'));

    $response->assertStatus(503);
    $this->assertDatabaseMissing('custom_casts_products', ['name' => 'Demo Widget']);
});

it('appears on the dashboard grouped under Search & Data', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Search & Data', 'Custom Casts & value objects']);
});
