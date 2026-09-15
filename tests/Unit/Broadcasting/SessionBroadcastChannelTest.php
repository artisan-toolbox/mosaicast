<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Auth\GenericUser;

it('derives a stable opaque identifier and private channel name from a session', function (): void {
    $channel = new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions');

    expect($channel->identifier(str_repeat('a', 40)))
        ->toBe('9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681')
        ->and($channel->name(str_repeat('a', 40)))
        ->toBe('mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681');
});

it('rejects an empty session broadcast key', function (): void {
    new SessionBroadcastChannel('', 'mosaicast.sessions');
})->throws(InvalidArgumentException::class, 'The session broadcast key must not be empty.');

it('rejects an empty session ID', function (): void {
    new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions')->identifier('');
})->throws(InvalidArgumentException::class, 'The session ID must not be empty.');

it('rejects a malformed opaque session identifier', function (): void {
    new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions')
        ->nameForIdentifier('opaque-session-identifier');
})->throws(InvalidArgumentException::class, 'The session identifier must be a 64-character lowercase SHA-256 hash.');

it('authorizes only the identity that owns the session channel', function (): void {
    $channel = new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions');
    $sessionIdentifier = $channel->identifier(str_repeat('a', 40));

    expect($channel->join(new GenericUser(['id' => $sessionIdentifier]), $sessionIdentifier))
        ->toBeTrue()
        ->and($channel->join(
            new GenericUser(['id' => $sessionIdentifier]),
            $channel->identifier(str_repeat('b', 40)),
        ))
        ->toBeFalse();
});
