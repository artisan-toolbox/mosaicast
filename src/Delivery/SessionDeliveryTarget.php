<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Delivery;

use InvalidArgumentException;

/**
 * A serializable target for delivering an event to one browser session.
 */
final readonly class SessionDeliveryTarget
{
    public function __construct(public string $sessionIdentifier)
    {
        throw_unless(
            self::isValidIdentifier($this->sessionIdentifier),
            InvalidArgumentException::class,
            'The session identifier must be a 64-character lowercase SHA-256 hash.',
        );
    }

    public static function isValidIdentifier(string $sessionIdentifier): bool
    {
        return preg_match('/\\A[a-f0-9]{64}\\z/D', $sessionIdentifier) === 1;
    }
}
