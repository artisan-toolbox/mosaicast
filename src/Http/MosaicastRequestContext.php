<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Http;

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use ArtisanToolbox\Mosaicast\Delivery\SessionDeliveryTarget;
use ArtisanToolbox\Mosaicast\Events\MosaicastEvent;
use Illuminate\Http\Request;

final class MosaicastRequestContext
{
    /** @var list<MosaicastEvent> */
    private array $events = [];

    private bool $finalized = false;

    private ?SessionDeliveryTarget $target = null;

    public function __construct(private readonly SessionBroadcastChannel $channel) {}

    public function initialize(Request $request): void
    {
        if (! $request->hasSession()) {
            $this->target = null;

            return;
        }

        $sessionId = $request->session()->getId();

        $this->target = $sessionId === ''
            ? null
            : new SessionDeliveryTarget($this->channel->identifier($sessionId));
    }

    public function hasSession(): bool
    {
        return $this->target !== null;
    }

    public function target(): ?SessionDeliveryTarget
    {
        return $this->target;
    }

    public function sessionIdentifier(): ?string
    {
        return $this->target?->sessionIdentifier;
    }

    public function add(MosaicastEvent $event): void
    {
        $this->events[] = $event;
    }

    public function finalized(): bool
    {
        return $this->finalized;
    }

    /**
     * @return list<MosaicastEvent>
     */
    public function finalize(): array
    {
        $this->finalized = true;

        return $this->pull();
    }

    /**
     * @return list<MosaicastEvent>
     */
    public function pull(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /**
     * @return list<array{name: string, payload: array<string, mixed>}>
     */
    public function pullForInertia(): array
    {
        return array_map(
            static fn (MosaicastEvent $event): array => $event->toArray(),
            $this->pull(),
        );
    }
}
