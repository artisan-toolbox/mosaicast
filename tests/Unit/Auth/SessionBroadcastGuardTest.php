<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Auth\SessionBroadcastGuard;
use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

it('authenticates a session as an opaque broadcast identity', function (): void {
    $session = new Store('test', new ArraySessionHandler(120));
    $session->setId(str_repeat('a', 40));
    $request = Request::create('/broadcasting/auth', 'POST');
    $request->setLaravelSession($session);
    $guard = new SessionBroadcastGuard(
        $request,
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );

    expect($guard->id())
        ->toBe('9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681')
        ->and($guard->check())->toBeTrue()
        ->and($guard->guest())->toBeFalse()
        ->and($guard->hasUser())->toBeTrue();
});

it('rejects a request without a session', function (): void {
    $guard = new SessionBroadcastGuard(
        Request::create('/broadcasting/auth', 'POST'),
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );

    expect($guard->user())->toBeNull()
        ->and($guard->check())->toBeFalse()
        ->and($guard->guest())->toBeTrue()
        ->and($guard->hasUser())->toBeFalse()
        ->and($guard->validate())->toBeFalse();
});

it('resolves a new identity when the request changes', function (): void {
    $firstSession = new Store('test', new ArraySessionHandler(120));
    $firstSession->setId(str_repeat('a', 40));
    $firstRequest = Request::create('/broadcasting/auth', 'POST');
    $firstRequest->setLaravelSession($firstSession);
    $guard = new SessionBroadcastGuard(
        $firstRequest,
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );
    $firstIdentifier = $guard->id();

    $secondSession = new Store('test', new ArraySessionHandler(120));
    $secondSession->setId(str_repeat('b', 40));
    $secondRequest = Request::create('/broadcasting/auth', 'POST');
    $secondRequest->setLaravelSession($secondSession);

    expect($guard->setRequest($secondRequest)->id())->not->toBe($firstIdentifier);
});
