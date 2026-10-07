<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Mosaicast;

if (! function_exists('mosaicast')) {
    /**
     * Dispatch an event through Mosaicast, or access its fluent API.
     *
     * Call without an event to select an explicit session target, such as from a
     * queued job: `mosaicast()->toSession($sessionIdentifier)->dispatch(...)`.
     *
     * @param  array<string, mixed>  $payload
     */
    function mosaicast(string|object|null $event = null, array $payload = []): Mosaicast
    {
        $mosaicast = resolve(Mosaicast::class);

        if ($event !== null) {
            $mosaicast->dispatch($event, $payload);
        }

        return $mosaicast;
    }
}
