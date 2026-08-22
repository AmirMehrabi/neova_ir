<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TodayRealtimeChanged implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public int $workspaceId,
        public string $mutation,
        public ?int $actorId = null,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('workspace.'.$this->workspaceId.'.today');
    }

    public function broadcastAs(): string
    {
        return 'today.changed';
    }

    public function broadcastWith(): array
    {
        return ['workspaceId' => $this->workspaceId, 'mutation' => $this->mutation, 'actorId' => $this->actorId, 'occurredAt' => now()->toIso8601String()];
    }
}
