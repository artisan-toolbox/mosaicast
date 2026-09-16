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
- confirm web routes start a Laravel session and the app has a configured broadcaster and `/broadcasting/auth` route

### 2. Install and dispatch

- install `artisan-toolbox/mosaicast` with Composer; Laravel discovers its provider automatically
- use `ArtisanToolbox\Mosaicast\Facades\Mosaicast::dispatch('event.name', $payload)` inside a session-backed request, or dispatch a Laravel event object
- expect events in `mosaicast.events` when Inertia resolves that shared prop; other responses broadcast pending events to the current session's private channel
- for a job, capture `Mosaicast::currentSessionIdentifier()` during the request and call `Mosaicast::toSession($identifier)->dispatch(...)` in the job

### 3. Connect the Vue client

- install `@artisan-toolbox/mosaicast` using `file:vendor/artisan-toolbox/mosaicast` after Composer installation
- keep `@laravel/echo-vue`, `laravel-echo`, and `pusher-js` in the host application's JavaScript dependencies when using Reverb
- configure Echo in the host application and pass `echo()` to `createMosaicast()` inside the existing Inertia `withApp` callback on the client
- register listeners with `mosaicast().on(name, (payload, source) => ...)` and call its unsubscribe function when a component is unmounted
- when the backend channel prefix changes, pass the matching `channelPrefix` option to the Vue plugin

## Rules, References, and Templates

Read before executing:

- `https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/`
- `https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/inertia-delivery/`
- `https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/session-channels/`
- `https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/vue-plugin/`

## Examples

```php
use ArtisanToolbox\Mosaicast\Facades\Mosaicast;

Mosaicast::dispatch('orders.updated', ['orderId' => $order->id]);

$sessionIdentifier = Mosaicast::currentSessionIdentifier();
// Pass the identifier to a queued job, then inside that job:
Mosaicast::toSession($sessionIdentifier)->dispatch('orders.updated', ['orderId' => $orderId]);
```

```ts
import { createMosaicast, mosaicast } from '@artisan-toolbox/mosaicast';
import { echo } from '@laravel/echo-vue';
import type { App } from 'vue';

export function attachMosaicast(app: App): () => void {
  app.use(createMosaicast({ echo: echo() }));

  return mosaicast().on('orders.updated', (payload, source) => {
    console.log(payload.orderId, source);
  });
}
```

## Anti-patterns

- do not call implicit `Mosaicast::dispatch()` in a job without an active request session
- do not pass or expose the raw Laravel session ID as a channel target; use the opaque identifier returned by Mosaicast
- do not treat the session broadcast guard as application user authorization
- do not assume a broadcast can reach a client that subscribed after the event was sent
- do not add a second channel rule or Mosaicast-specific middleware; the package registers its rule and Inertia props
