<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Concerns;

use ArtisanToolbox\Mosaicast\Mosaicast;
use ReflectionClass;

/**
 * Adds a static shortcut for dispatching an event through Mosaicast.
 */
trait DispatchesMosaicast
{
    public static function mosaicast(mixed ...$arguments): void
    {
        $event = (new ReflectionClass(static::class))->newInstanceArgs($arguments);

        resolve(Mosaicast::class)->dispatch($event);
    }
}
