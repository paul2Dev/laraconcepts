<?php

use App\Modules\LiveNotifications\LiveNotificationsServiceProvider;
use App\Modules\LiveNotifications\Notifications\DemoNotificationPosted;
use Illuminate\Support\Facades\Notification;
use Laravel\Pennant\Feature;

it('broadcasts a demo notification on its own channel when the flag is on', function () {
    Notification::fake();
    Feature::activate(LiveNotificationsServiceProvider::SLUG);

    $response = $this->postJson(route('live-notifications.notify'));

    $response->assertOk();

    Notification::assertSentOnDemand(
        DemoNotificationPosted::class,
        fn ($notification, $channels) => in_array('broadcast', $channels, true)
            && $notification->broadcastOn()[0]->name === DemoNotificationPosted::CHANNEL
    );
});

it('refuses to send a notification when the flag is off', function () {
    Notification::fake();
    Feature::deactivate(LiveNotificationsServiceProvider::SLUG);

    $response = $this->postJson(route('live-notifications.notify'));

    $response->assertStatus(503);
    $response->assertJson(['message' => 'unavailable']);
    Notification::assertNothingSent();
});

it('blocks the demo page itself when the flag is off', function () {
    Feature::deactivate(LiveNotificationsServiceProvider::SLUG);

    $response = $this->get(route('live-notifications.demo'));

    $response->assertStatus(503);
});

it('renders the demo page when the flag is on', function () {
    Feature::activate(LiveNotificationsServiceProvider::SLUG);

    $response = $this->get(route('live-notifications.demo'));

    $response->assertOk();
});

it('appears on the dashboard grouped under Real-time', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Real-time', 'Live Notifications']);
});
