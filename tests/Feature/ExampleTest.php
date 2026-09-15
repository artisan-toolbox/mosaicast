<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Mosaicast;

it('resolves the singleton', function () {
    expect(app(Mosaicast::class))->toBeInstanceOf(Mosaicast::class);
});

it('returns the same instance from the container', function () {
    expect(app(Mosaicast::class))->toBe(app(Mosaicast::class));
});

it('merges the package config', function () {
    expect(config('mosaicast.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('mosaicast::messages.placeholder'))->toBe('Mosaicast placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('mosaicast::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('mosaicast:placeholder')
        ->expectsOutputToContain('Mosaicast placeholder command executed.')
        ->assertSuccessful();
});
