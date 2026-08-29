<?php

namespace App\Modules\LiveNotifications\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DemoNotificationPosted extends Notification
{
    public const CHANNEL = 'live-notifications';

    public function __construct(
        public readonly string $title,
        public readonly string $body,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel(self::CHANNEL)];
    }

    public function broadcastAs(): string
    {
        return 'notification.posted';
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
