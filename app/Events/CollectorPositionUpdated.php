<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollectorPositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $collectorId,
        public int $communeId,
        public float $lat,
        public float $lng,
        public ?string $timestamp = null
    ) {
        $this->timestamp = $timestamp ?? now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tracking.commune.' . $this->communeId),
            new Channel('collector.' . $this->collectorId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'position.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'collector_id' => $this->collectorId,
            'commune_id' => $this->communeId,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'timestamp' => $this->timestamp,
        ];
    }
}
