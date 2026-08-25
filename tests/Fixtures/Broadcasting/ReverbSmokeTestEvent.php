<?php

namespace Tests\Fixtures\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ReverbSmokeTestEvent implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public readonly string $message) {}

    public function broadcastOn(): array
    {
        return [new Channel('reverb-smoke-test')];
    }
}
