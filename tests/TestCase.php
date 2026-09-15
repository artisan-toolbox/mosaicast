<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Tests;

use ArtisanToolbox\Mosaicast\MosaicastServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MosaicastServiceProvider::class,
        ];
    }
}
