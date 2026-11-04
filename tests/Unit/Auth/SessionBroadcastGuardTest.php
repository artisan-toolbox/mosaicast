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
    $session->put('_token', 'test-session-token');
    $request = Request::create('/broadcasting/auth', 'POST');
    $request->setLaravelSession($session);
    $guard = new SessionBroadcastGuard(
        $request,
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );

    expect($guard->id())
        ->toBe('7a2ec00f1db6d65aa29a522c84e9d1ac594b7f7bdfe63050300ea8164289ecac')
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
    $firstSession->put('_token', 'test-session-token');
    $firstRequest = Request::create('/broadcasting/auth', 'POST');
    $firstRequest->setLaravelSession($firstSession);
    $guard = new SessionBroadcastGuard(
        $firstRequest,
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );
    $firstIdentifier = $guard->id();

    $secondSession = new Store('test', new ArraySessionHandler(120));
    $secondSession->setId(str_repeat('b', 40));
    $secondSession->put('_token', 'test-session-token');
    $secondRequest = Request::create('/broadcasting/auth', 'POST');
    $secondRequest->setLaravelSession($secondSession);

    expect($guard->setRequest($secondRequest)->id())->not->toBe($firstIdentifier);
});

it('rejects the old channel when an invalidated cookie starts a new guest session', function (): void {
    $handler = new ArraySessionHandler(120);
    $session = new Store('test', $handler, str_repeat('a', 40));
    $session->start();
    $session->save();
    $request = Request::create('/broadcasting/auth', 'POST');
    $request->setLaravelSession($session);
    $channel = new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions');
    $guard = new SessionBroadcastGuard($request, $channel);
    $oldSessionId = $session->getId();
    $oldIdentifier = $guard->id();

    $session->invalidate();
    $replayed = new Store('test', $handler, $oldSessionId);
    $replayed->start();
    $replayRequest = Request::create('/broadcasting/auth', 'POST');
    $replayRequest->setLaravelSession($replayed);
    $guard->setRequest($replayRequest);

    expect($guard->id())->toBeString()->not->toBe($oldIdentifier)
        ->and($channel->join($guard->user(), $oldIdentifier))->toBeFalse()
        ->and($channel->join($guard->user(), $guard->id()))->toBeTrue();
});

it('refreshes the broadcast identity when the active session rotates in the same request', function (): void {
    $session = new Store('test', new ArraySessionHandler(120), str_repeat('a', 40));
    $session->start();
    $request = Request::create('/broadcasting/auth', 'POST');
    $request->setLaravelSession($session);
    $guard = new SessionBroadcastGuard(
        $request,
        new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions'),
    );
    $oldIdentifier = $guard->id();

    $session->regenerate(true);
    $regeneratedIdentifier = $guard->id();
    expect($regeneratedIdentifier)->toBeString()->not->toBe($oldIdentifier);

    $session->regenerateToken();
    expect($guard->id())->toBeString()->not->toBe($regeneratedIdentifier);

    $session->invalidate();
    expect($guard->id())->toBeNull();
});
