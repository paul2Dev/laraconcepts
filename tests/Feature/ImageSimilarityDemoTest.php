<?php

use App\Modules\ImageSimilarity\Database\Seeders\SeededImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

function seededImageUpload(string $filename): UploadedFile
{
    return new UploadedFile(SeededImageSeeder::path($filename), "{$filename}.jpg", 'image/jpeg', null, true);
}

it('ranks seeded images by ascending distance when the flag is on', function () {
    Feature::activate('image-similarity');

    $response = $this->postJson(route('image-similarity.upload'), ['image' => seededImageUpload('forest-river')]);

    $response->assertOk();
    $results = $response->json('results');

    expect($results)->not->toBeEmpty();
    expect($results[0]['label'])->toBe('Forest River');
    expect($results[0]['distance'])->toBeLessThan(0.01);

    $distances = array_column($results, 'distance');
    expect($distances)->toBe(collect($distances)->sort()->values()->all());
});

it('refuses to run the demo when the flag is off', function () {
    Feature::deactivate('image-similarity');

    $response = $this->postJson(route('image-similarity.upload'), ['image' => seededImageUpload('forest-river')]);

    $response->assertStatus(503);
});

it('appears on the dashboard grouped under Search & AI', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Search & AI', 'Image Similarity']);
});
