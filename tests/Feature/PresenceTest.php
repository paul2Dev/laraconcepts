<?php

use App\Modules\Presence\PresenceServiceProvider;
use App\Platform\ConceptRegistry;
use Illuminate\Testing\TestResponse;
use Laravel\Pennant\Feature;

/**
 * The channel-join gate lives on `Broadcast::channel()`, which is only
 * registered against whichever connection was the default at boot time.
 * Tests run with `BROADCAST_CONNECTION=null` (a no-op broadcaster), so the
 * provider's `boot()` is re-run here after switching to the real `reverb`
 * driver — reusing fake but well-formed credentials, since Pusher's auth
 * signing is pure local HMAC and never makes a network call.
 */
function useReverbBroadcasting(): void
{
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app-id',
    ]);

    app()->getProvider(PresenceServiceProvider::class)->boot(app(ConceptRegistry::class));
}

function joinPresenceChannel(): TestResponse
{
    return test()->postJson('/broadcasting/auth', [
        'channel_name' => 'presence-'.PresenceServiceProvider::CHANNEL,
        'socket_id' => '123.456',
    ]);
}

function withGuestSession(): void
{
    test()->withSession([
        PresenceServiceProvider::SESSION_GUEST => ['id' => 'guest-1', 'name' => 'Curious Otter'],
    ]);
}

it('rejects joining the presence channel when the flag is off', function () {
    useReverbBroadcasting();
    Feature::deactivate(PresenceServiceProvider::SLUG);
    withGuestSession();

    $response = joinPresenceChannel();

    $response->assertStatus(403);
});

it('authorizes joining the presence channel when the flag is on', function () {
    useReverbBroadcasting();
    Feature::activate(PresenceServiceProvider::SLUG);
    withGuestSession();

    $response = joinPresenceChannel();

    $response->assertOk();
    $response->assertJsonStructure(['auth', 'channel_data']);

    $channelData = json_decode($response->json('channel_data'), true);
    expect($channelData['user_info']['name'])->toBe('Curious Otter');
});

it('refuses to join the presence channel without a guest session', function () {
    useReverbBroadcasting();
    Feature::activate(PresenceServiceProvider::SLUG);

    $response = joinPresenceChannel();

    $response->assertStatus(403);
});

it('blocks the demo page itself when the flag is off', function () {
    Feature::deactivate(PresenceServiceProvider::SLUG);

    $response = $this->get(route('presence.demo'));

    $response->assertStatus(503);
});

it('renders the demo page and assigns a guest identity when the flag is on', function () {
    Feature::activate(PresenceServiceProvider::SLUG);

    $response = $this->get(route('presence.demo'));

    $response->assertOk();
    $this->assertNotNull(session(PresenceServiceProvider::SESSION_GUEST));
});

it('appears on the dashboard grouped under Real-time', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Real-time', 'Presence']);
});
