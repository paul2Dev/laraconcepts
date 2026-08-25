<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('finds a semantically related result that a plain keyword match would miss when the flag is on', function () {
    Feature::activate('semantic-search');

    $keywordResponse = $this->getJson(route('semantic-search.demo', ['query' => 'notebook', 'mode' => 'keyword']));
    $semanticResponse = $this->getJson(route('semantic-search.demo', ['query' => 'notebook', 'mode' => 'semantic']));

    $keywordResponse->assertOk();
    expect($keywordResponse->json('results'))->toBeEmpty();

    $semanticResponse->assertOk();
    $semanticTitles = array_column($semanticResponse->json('results'), 'title');
    expect($semanticTitles)->toContain('UltraBook Pro 14');
});

it('runs with a demonstrative default when the demo route is hit with no query string', function () {
    Feature::activate('semantic-search');

    $response = $this->getJson(route('semantic-search.demo'));

    $response->assertOk();
    $response->assertJson(['query' => 'notebook', 'mode' => 'semantic']);
    expect($response->json('results'))->not->toBeEmpty();
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('semantic-search');

    $response = $this->getJson(route('semantic-search.demo', ['query' => 'notebook', 'mode' => 'semantic']));

    $response->assertStatus(503);
});

it('appears on the dashboard grouped under Search & AI', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Search & AI', 'Semantic Search']);
});
