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

    private bool $hasInertiaEvents = false;

    private ?SessionDeliveryTarget $target = null;

    private ?string $sessionId = null;

    private ?string $sessionToken = null;

    public function __construct(private readonly SessionBroadcastChannel $channel) {}

    public function initialize(Request $request): void
    {
        $session = $request->hasSession() ? $request->session() : null;
        $sessionId = $session?->getId();
        $sessionToken = $session?->token();

        if ($sessionId === $this->sessionId && $sessionToken === $this->sessionToken) {
            return;
        }

        $this->sessionId = $sessionId;
        $this->sessionToken = $sessionToken;
        $this->target = $sessionId === null || $sessionId === '' || $sessionToken === null || $sessionToken === ''
            ? null
            : new SessionDeliveryTarget($this->channel->identifier($sessionId, $sessionToken));
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
        $events = $this->pull();
        $this->hasInertiaEvents = $events !== [];

        return array_map(
            static fn (MosaicastEvent $event): array => $event->toArray(),
            $events,
        );
    }

    public function hasInertiaEvents(): bool
    {
        return $this->hasInertiaEvents;
    }
}
