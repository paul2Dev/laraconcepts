<?php

use App\Modules\JobProgress\Events\UploadProgressUpdated;
use App\Modules\JobProgress\JobProgressServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('broadcasts increasing progress events ending at 100 when the flag is on', function () {
    Storage::fake('local');
    Event::fake([UploadProgressUpdated::class]);
    Feature::activate(JobProgressServiceProvider::SLUG);

    $file = UploadedFile::fake()->createWithContent('rows.csv', str_repeat("row,value\n", 4000));

    $response = $this->post(route('job-progress.upload'), ['file' => $file]);

    $response->assertOk();

    $percentages = collect(Event::dispatched(UploadProgressUpdated::class))
        ->map(fn (array $args) => $args[0]->percentage)
        ->values();

    expect($percentages)->not->toBeEmpty();
    expect($percentages->last())->toBe(100);
    expect($percentages->all())->toBe($percentages->unique()->sort()->values()->all());
});

it('refuses to accept an upload when the flag is off', function () {
    Storage::fake('local');
    Feature::deactivate(JobProgressServiceProvider::SLUG);

    $response = $this->post(route('job-progress.upload'), [
        'file' => UploadedFile::fake()->create('rows.csv', 10),
    ]);

    $response->assertStatus(503);
    $response->assertJson(['message' => 'unavailable']);
});

it('blocks the demo page itself when the flag is off', function () {
    Feature::deactivate(JobProgressServiceProvider::SLUG);

    $response = $this->get(route('job-progress.demo'));

    $response->assertStatus(503);
});

it('renders the demo page when the flag is on', function () {
    Feature::activate(JobProgressServiceProvider::SLUG);

    $response = $this->get(route('job-progress.demo'));

    $response->assertOk();
});

it('appears on the dashboard grouped under Real-time', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Real-time', 'Job Progress']);
});
