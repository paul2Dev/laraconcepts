<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('ranks seeded images by ascending distance for a text query with no literal filename overlap', function () {
    Feature::activate('image-text-search');

    $response = $this->getJson(route('image-text-search.demo', ['query' => 'trees by calm water']));

    $response->assertOk();
    $results = $response->json('results');

    expect($results)->not->toBeEmpty();
    expect($results[0]['label'])->toBe('Lake Forest');
    expect($results[0]['distance'])->toBeLessThan(0.01);

    $distances = array_column($results, 'distance');
    expect($distances)->toBe(collect($distances)->sort()->values()->all());
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('image-text-search');

    $response = $this->getJson(route('image-text-search.demo', ['query' => 'trees by calm water']));

    $response->assertStatus(503);
});

it('appears on the dashboard grouped under Search & AI', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Search & AI', 'Image Text Search']);
});
