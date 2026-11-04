<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Facades;

use ArtisanToolbox\Mosaicast\Delivery\MosaicastDispatcher;
use Illuminate\Support\Facades\Facade;

/**
 * @see MosaicastDispatcher
 */
class Mosaicast extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MosaicastDispatcher::class;
    }
}
