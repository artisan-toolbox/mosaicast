<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Auth\SessionBroadcastGuard;
use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Support\Facades\Auth;

it('registers the session broadcast guard and channel service', function (): void {
    expect(config('auth.guards.mosaicast-session.driver'))->toBe('mosaicast-session')
        ->and(Auth::guard('mosaicast-session'))->toBeInstanceOf(SessionBroadcastGuard::class)
        ->and(resolve(SessionBroadcastChannel::class))->toBeInstanceOf(SessionBroadcastChannel::class);
});
