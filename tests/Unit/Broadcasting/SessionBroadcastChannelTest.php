<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Auth\GenericUser;

it('derives a stable opaque identifier and private channel name from a session', function (): void {
    $channel = new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions');

    expect($channel->identifier(str_repeat('a', 40), 'test-session-token'))
        ->toBe('7a2ec00f1db6d65aa29a522c84e9d1ac594b7f7bdfe63050300ea8164289ecac')
        ->and($channel->name(str_repeat('a', 40), 'test-session-token'))
        ->toBe('mosaicast.sessions.7a2ec00f1db6d65aa29a522c84e9d1ac594b7f7bdfe63050300ea8164289ecac');
});

it('rejects an empty session broadcast key', function (): void {
    new SessionBroadcastChannel('', 'mosaicast.sessions');
})->throws(InvalidArgumentException::class, 'The session broadcast key must not be empty.');

it('rejects an empty session ID', function (): void {
    new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions')->identifier('', 'test-session-token');
})->throws(InvalidArgumentException::class, 'The session ID must not be empty.');

it('rejects a malformed opaque session identifier', function (): void {
    new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions')
        ->nameForIdentifier('opaque-session-identifier');
})->throws(InvalidArgumentException::class, 'The session identifier must be a 64-character lowercase SHA-256 hash.');

it('authorizes only the identity that owns the session channel', function (): void {
    $channel = new SessionBroadcastChannel('test-session-broadcast-key', 'mosaicast.sessions');
    $sessionIdentifier = $channel->identifier(str_repeat('a', 40), 'test-session-token');

    expect($channel->join(new GenericUser(['id' => $sessionIdentifier]), $sessionIdentifier))
        ->toBeTrue()
        ->and($channel->join(
            new GenericUser(['id' => $sessionIdentifier]),
            $channel->identifier(str_repeat('b', 40), 'test-session-token'),
        ))
        ->toBeFalse();
});
