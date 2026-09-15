<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Delivery;

use ArtisanToolbox\Mosaicast\Events\MosaicastEvent;

/**
 * Dispatches events directly to an explicit delivery target.
 */
final readonly class TargetedMosaicast
{
    public function __construct(
        private SessionDeliveryTarget $target,
        private MosaicastBroadcaster $broadcaster,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string|object $event, array $payload = []): void
    {
        $this->broadcaster->dispatch($this->target, MosaicastEvent::from($event, $payload));
    }
}
