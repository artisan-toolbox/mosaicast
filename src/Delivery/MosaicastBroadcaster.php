<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Delivery;

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use ArtisanToolbox\Mosaicast\Events\BroadcastMosaicastEvent;
use ArtisanToolbox\Mosaicast\Events\MosaicastEvent;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Delivers Mosaicast envelopes to a session's private broadcast channel.
 */
final readonly class MosaicastBroadcaster
{
    public function __construct(
        private SessionBroadcastChannel $channel,
        private Dispatcher $events,
    ) {}

    public function dispatch(SessionDeliveryTarget $target, MosaicastEvent $event): void
    {
        $this->events->dispatch(new BroadcastMosaicastEvent(
            $event,
            $this->channel->privateChannel($target),
        ));
    }
}
