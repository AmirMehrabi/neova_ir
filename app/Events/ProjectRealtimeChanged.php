<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ProjectRealtimeChanged implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public int $projectId,
        public string $mutation,
        public ?int $actorId = null,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('project.'.$this->projectId);
    }

    public function broadcastAs(): string
    {
        return 'project.changed';
    }

    public function broadcastWith(): array
    {
        return ['projectId' => $this->projectId, 'mutation' => $this->mutation, 'actorId' => $this->actorId, 'occurredAt' => now()->toIso8601String()];
    }
}
