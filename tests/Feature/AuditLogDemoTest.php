<?php

use App\Modules\AuditLog\AuditLogServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('records an audit log entry for a CRUD action and surfaces it in the feed when the flag is on', function () {
    Feature::activate(AuditLogServiceProvider::SLUG);

    $store = $this->postJson(route('audit-log.notes.store'), ['title' => 'Buy milk']);
    $store->assertOk();
    $noteId = $store->json('notes.0.id');

    $this->assertDatabaseHas('audit_log_entries', [
        'action' => 'created',
        'subject' => "Note #{$noteId}: Buy milk",
    ]);

    $feed = $this->getJson(route('audit-log.feed'));

    $feed->assertOk();
    expect($feed->json('entries.0'))
        ->action->toBe('created')
        ->subject->toBe("Note #{$noteId}: Buy milk");
});

it('refuses to run either demo route when the flag is off', function () {
    Feature::deactivate(AuditLogServiceProvider::SLUG);

    $store = $this->postJson(route('audit-log.notes.store'), ['title' => 'Buy milk']);
    $store->assertStatus(503);

    $feed = $this->getJson(route('audit-log.feed'));
    $feed->assertStatus(503);
});

it('appears on the dashboard grouped under DevOps / Observability', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['DevOps / Observability', 'Audit Log']);
});
