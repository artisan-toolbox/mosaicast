<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Broadcasting;

use ArtisanToolbox\Mosaicast\Delivery\SessionDeliveryTarget;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

final readonly class SessionBroadcastChannel
{
    public function __construct(
        private string $key,
        private string $prefix,
    ) {
        throw_if($this->key === '', InvalidArgumentException::class, 'The session broadcast key must not be empty.');
        throw_if($this->prefix === '', InvalidArgumentException::class, 'The session broadcast channel prefix must not be empty.');
    }

    public function identifier(string $sessionId): string
    {
        throw_if($sessionId === '', InvalidArgumentException::class, 'The session ID must not be empty.');

        return hash_hmac('sha256', $sessionId, $this->key);
    }

    public function name(string $sessionId): string
    {
        return $this->nameForIdentifier($this->identifier($sessionId));
    }

    public function nameForIdentifier(string $sessionIdentifier): string
    {
        throw_unless(
            SessionDeliveryTarget::isValidIdentifier($sessionIdentifier),
            InvalidArgumentException::class,
            'The session identifier must be a 64-character lowercase SHA-256 hash.',
        );

        return $this->prefix.'.'.$sessionIdentifier;
    }

    public function privateChannel(SessionDeliveryTarget $target): PrivateChannel
    {
        return new PrivateChannel($this->nameForIdentifier($target->sessionIdentifier));
    }

    public function join(Authenticatable $identity, string $sessionIdentifier): bool
    {
        return hash_equals((string) $identity->getAuthIdentifier(), $sessionIdentifier);
    }
}
