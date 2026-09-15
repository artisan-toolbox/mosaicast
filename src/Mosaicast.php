<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast;

use ArtisanToolbox\Maintainer\Versionable\Contracts\Versionable;
use ArtisanToolbox\Mosaicast\Delivery\MosaicastBroadcaster;
use ArtisanToolbox\Mosaicast\Delivery\SessionDeliveryTarget;
use ArtisanToolbox\Mosaicast\Delivery\TargetedMosaicast;
use ArtisanToolbox\Mosaicast\Events\MosaicastEvent;
use ArtisanToolbox\Mosaicast\Http\MosaicastRequestContext;
use Illuminate\Http\Request;
use LogicException;

class Mosaicast implements Versionable
{
    public const string VERSION = '1.0.0';

    public function __construct(
        private readonly MosaicastRequestContext $context,
        private readonly MosaicastBroadcaster $broadcaster,
        private readonly Request $request,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string|object $event, array $payload = []): void
    {
        $this->context->initialize($this->request);

        throw_unless($this->context->hasSession(), LogicException::class, 'Mosaicast events require an active session request. Use Mosaicast::toSession($sessionIdentifier)->dispatch() outside a request.');

        $event = MosaicastEvent::from($event, $payload);

        if ($this->context->finalized()) {
            $this->broadcaster->dispatch($this->currentTarget(), $event);

            return;
        }

        $this->context->add($event);
    }

    public function currentTarget(): SessionDeliveryTarget
    {
        $this->context->initialize($this->request);

        return $this->context->target()
            ?? throw new LogicException('Mosaicast cannot derive a delivery target without an active session request.');
    }

    public function currentSessionIdentifier(): string
    {
        return $this->currentTarget()->sessionIdentifier;
    }

    public function toSession(string $sessionIdentifier): TargetedMosaicast
    {
        return new TargetedMosaicast(
            new SessionDeliveryTarget($sessionIdentifier),
            $this->broadcaster,
        );
    }
}
