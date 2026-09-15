<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final readonly class BroadcastMosaicastEvent implements ShouldBroadcastNow
{
    public function __construct(
        private MosaicastEvent $event,
        private PrivateChannel $channel,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return $this->channel;
    }

    public function broadcastAs(): string
    {
        return $this->event->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->event->payload;
    }
}
