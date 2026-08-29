<?php

namespace App\Modules\LiveNotifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LiveNotifications\LiveNotificationsServiceProvider;
use App\Modules\LiveNotifications\Notifications\DemoNotificationPosted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Laravel\Pennant\Feature;

class LiveNotificationsDemoController extends Controller
{
    /** @var list<array{title: string, body: string}> */
    private const SAMPLES = [
        ['title' => 'New comment', 'body' => 'Someone replied to your post.'],
        ['title' => 'Deploy finished', 'body' => 'Production was deployed successfully.'],
        ['title' => 'Payment received', 'body' => 'A new invoice was just paid.'],
        ['title' => 'Mention', 'body' => 'You were mentioned in a thread.'],
    ];

    public function show(): Response
    {
        $active = $this->isActive();

        return response()
            ->view('live-notifications::demo', ['active' => $active])
            ->setStatusCode($active ? 200 : 503);
    }

    public function notify(): JsonResponse
    {
        if (! $this->isActive()) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $sample = Arr::random(self::SAMPLES);

        Notification::send(new AnonymousNotifiable, new DemoNotificationPosted($sample['title'], $sample['body']));

        return response()->json(['message' => 'sent']);
    }

    private function isActive(): bool
    {
        return Feature::active(LiveNotificationsServiceProvider::SLUG);
    }
}
