<?php

namespace App\Modules\LiveCollab\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentEdited implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public readonly int $documentId,
        public readonly string $content,
        public readonly string $clientId,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel(self::channelName($this->documentId))];
    }

    public static function channelName(int $documentId): string
    {
        return "live-collab.{$documentId}";
    }

    public function broadcastAs(): string
    {
        return 'document.edited';
    }

    /** @return array<string, string> */
    public function broadcastWith(): array
    {
        return [
            'content' => $this->content,
            'client_id' => $this->clientId,
        ];
    }
}
