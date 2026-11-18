---
name: mosaicast-development
description: >
  Deliver Laravel events through Inertia props or private session broadcasting in Vue applications.
license: MIT
metadata:
  author: Allan Mariucci Carvalho
---

# Mosaicast

Use this skill when a Laravel application needs to integrate the Mosaicast package.

## Primary Goal

- configure `artisan-toolbox/mosaicast` so one event contract reaches the client through Inertia or Laravel broadcasting

## Workflow

### 1. Inspect the Laravel app context

- confirm the app supports PHP 8.5 and Laravel 13
- identify the web routes, Inertia responses, queued jobs, and Vue entry point that need events
- confirm web routes start a Laravel session. Check the broadcaster and `/broadcasting/auth` route only for private-channel delivery

### 2. Install and dispatch

- install `artisan-toolbox/mosaicast` with Composer; Laravel discovers its provider automatically
- use `ArtisanToolbox\Mosaicast\Facades\Mosaicast::dispatch('event.name', $payload)` inside a session-backed request, or dispatch a Laravel event object
- expect events in `mosaicast.events` when Inertia resolves that shared prop; other responses broadcast pending events to the current session's private channel
- for a job, capture `Mosaicast::currentSessionIdentifier()` during the request and call `Mosaicast::toSession($identifier)->dispatch(...)` in the job

### 3. Connect the Vue client

- install `@artisan-toolbox/mosaicast` using `file:vendor/artisan-toolbox/mosaicast` after Composer installation
- register `createMosaicast()` inside the existing client-side Inertia `withApp` callback for Inertia delivery
- for private broadcasts over Reverb, install `@laravel/echo-vue`, `laravel-echo`, and `pusher-js`. Configure Echo and pass its instance to `createMosaicast({ echo: echo() })`
- register listeners with `mosaicast().on(name, (payload, source) => ...)` and call its unsubscribe function when a component is unmounted
- when the backend channel prefix changes, pass the matching `channelPrefix` option to the Vue plugin

## Rules, References, and Templates

Read before executing:

- [Installation and first Inertia event](https://atb.dev.br/docs/mosaicast/installation)
- `https://atb.dev.br/packages/mosaicast/inertia-delivery/`
- `https://atb.dev.br/packages/mosaicast/session-channels/`
- `https://atb.dev.br/packages/mosaicast/vue-plugin/`

## Examples

Inside an existing session-backed controller, dispatch the event before returning the Inertia response that reads `mosaicast.events`.

```php
use ArtisanToolbox\Mosaicast\Facades\Mosaicast;

Mosaicast::dispatch('orders.updated', ['orderId' => 42]);
```

```ts
import { createMosaicast, mosaicast } from '@artisan-toolbox/mosaicast';
import type { App } from 'vue';

export function attachMosaicast(app: App): () => void {
  app.use(createMosaicast());

  return mosaicast().on('orders.updated', (payload, source) => {
    console.log(payload.orderId, source);
  });
}
```

When the Inertia response includes `mosaicast.events`, the listener logs `42` and the delivery source. Register it before the response arrives and unsubscribe during component teardown.

For private delivery from a queued job, capture `Mosaicast::currentSessionIdentifier()` in the session-backed controller. Pass that value as `$sessionIdentifier` to the job. With private broadcasting configured, dispatch inside the job:

```php
use ArtisanToolbox\Mosaicast\Facades\Mosaicast;

Mosaicast::toSession($sessionIdentifier)->dispatch('orders.updated', ['orderId' => 42]);
```

## Anti-patterns

- do not call implicit `Mosaicast::dispatch()` in a job without an active request session
- do not pass or expose the raw Laravel session ID as a channel target; use the opaque identifier returned by Mosaicast
- do not treat the session broadcast guard as application user authorization
- do not assume a broadcast can reach a client that subscribed after the event was sent
- do not add a second channel rule or Mosaicast-specific middleware; the package registers its rule and Inertia props
