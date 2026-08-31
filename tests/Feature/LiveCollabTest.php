<?php

use App\Modules\LiveCollab\Events\DocumentEdited;
use App\Modules\LiveCollab\LiveCollabServiceProvider;
use App\Modules\LiveCollab\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('broadcasts an edit on the document channel when the flag is on', function () {
    Event::fake([DocumentEdited::class]);
    Feature::activate(LiveCollabServiceProvider::SLUG);

    $response = $this->postJson(route('live-collab.edit'), [
        'content' => 'Hello from session A',
        'client_id' => 'client-a',
    ]);

    $response->assertOk();

    $document = Document::sole();
    expect($document->content)->toBe('Hello from session A');

    Event::assertDispatched(
        DocumentEdited::class,
        fn (DocumentEdited $event) => $event->content === 'Hello from session A'
            && $event->clientId === 'client-a'
            && $event->broadcastOn()[0]->name === "live-collab.{$document->id}"
    );
});

it('refuses to accept an edit when the flag is off', function () {
    Event::fake([DocumentEdited::class]);
    Feature::deactivate(LiveCollabServiceProvider::SLUG);

    $response = $this->postJson(route('live-collab.edit'), [
        'content' => 'Should not be saved',
        'client_id' => 'client-a',
    ]);

    $response->assertStatus(503);
    $response->assertJson(['message' => 'unavailable']);
    Event::assertNotDispatched(DocumentEdited::class);
    expect(Document::count())->toBe(0);
});

it('blocks the demo page itself when the flag is off', function () {
    Feature::deactivate(LiveCollabServiceProvider::SLUG);

    $response = $this->get(route('live-collab.demo'));

    $response->assertStatus(503);
});

it('renders the demo page when the flag is on', function () {
    Feature::activate(LiveCollabServiceProvider::SLUG);

    $response = $this->get(route('live-collab.demo'));

    $response->assertOk();
});

it('appears on the dashboard grouped under Real-time', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Real-time', 'Live Collab']);
});
