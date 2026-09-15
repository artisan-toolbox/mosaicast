<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArtisanToolbox\Mosaicast\Mosaicast
 */
class Mosaicast extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ArtisanToolbox\Mosaicast\Mosaicast::class;
    }
}
