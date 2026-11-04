<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Auth\SessionBroadcastGuard;
use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use ArtisanToolbox\Mosaicast\MosaicastServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

it('registers the session broadcast guard and channel service', function (): void {
    expect(config('auth.guards.mosaicast-session.driver'))->toBe('mosaicast-session')
        ->and(Auth::guard('mosaicast-session'))->toBeInstanceOf(SessionBroadcastGuard::class)
        ->and(resolve(SessionBroadcastChannel::class))->toBeInstanceOf(SessionBroadcastChannel::class);
});

it('registers the channel even when Laravel caches HTTP routes', function (): void {
    app()->instance('routes.cached', true);
    Broadcast::purge();

    app()->getProvider(MosaicastServiceProvider::class)->boot();

    expect(Broadcast::getChannels()->get('mosaicast.sessions.{sessionIdentifier}'))
        ->toBe(SessionBroadcastChannel::class);
});
