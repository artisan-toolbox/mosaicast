<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Events;

use ArtisanToolbox\Mosaicast\Concerns\DispatchesMosaicast;

/**
 * Optional base class for events that provide the Mosaicast static shortcut.
 */
abstract class AbstractMosaicastEvent
{
    use DispatchesMosaicast;
}
