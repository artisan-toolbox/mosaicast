<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Tests;

use ArtisanToolbox\Mosaicast\MosaicastServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mosaicast.broadcasting.session_channel_key', 'test-session-broadcast-key');
    }

    protected function getPackageProviders($app): array
    {
        return [
            MosaicastServiceProvider::class,
        ];
    }
}
