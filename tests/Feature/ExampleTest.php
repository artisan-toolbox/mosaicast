<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Mosaicast;

it('resolves the singleton', function () {
    expect(resolve(Mosaicast::class))->toBeInstanceOf(Mosaicast::class);
});

it('returns the same instance from the container', function () {
    expect(resolve(Mosaicast::class))->toBe(resolve(Mosaicast::class));
});

it('merges the package configuration', function (): void {
    expect(config('mosaicast.broadcasting.guard'))->toBe('mosaicast-session');
});
