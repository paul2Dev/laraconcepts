<?php

namespace App\Modules\JobProgress\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UploadProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public readonly string $uploadId,
        public readonly int $percentage,
        public readonly int $linesProcessed,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel("job-progress.{$this->uploadId}")];
    }

    public function broadcastAs(): string
    {
        return 'progress.updated';
    }

    /** @return array<string, int> */
    public function broadcastWith(): array
    {
        return [
            'percentage' => $this->percentage,
            'lines_processed' => $this->linesProcessed,
        ];
    }
}
