<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Delivery\MosaicastDispatcher;
use ArtisanToolbox\Mosaicast\Facades\Mosaicast;

it('shares the scoped dispatcher between the container, facade, and helper', function (): void {
    $dispatcher = resolve(MosaicastDispatcher::class);

    expect($dispatcher)->toBeInstanceOf(MosaicastDispatcher::class)
        ->and(resolve(MosaicastDispatcher::class))->toBe($dispatcher)
        ->and(Mosaicast::getFacadeRoot())->toBe($dispatcher)
        ->and(mosaicast())->toBe($dispatcher);
});

it('merges the package configuration', function (): void {
    expect(config('mosaicast.broadcasting.guard'))->toBe('mosaicast-session');
});
