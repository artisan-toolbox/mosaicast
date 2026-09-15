# Mosaicast Engineering Checklist

The implemented behavior is documented in the [central Mosaicast guide](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/). This checklist tracks work still needed before a stable release; it is not a second API reference.

## Implemented

- [x] Dispatch event names and associative payloads or Laravel event objects through the PHP facade.
- [x] Honor `broadcastAs()`, `broadcastWith()`, and public event properties without exposing Laravel transport fields.
- [x] Provide the `DispatchesMosaicast` event trait and an optional base event class.
- [x] Collect events per request and consume them only when Inertia resolves `mosaicast.events`.
- [x] Broadcast unconsumed events when Laravel handles the response, and dispatch later events immediately to the current session.
- [x] Derive an opaque, session-scoped private channel and authorize it for guests or signed-in users through the session guard.
- [x] Capture the current opaque session identifier and use `toSession()` for explicit broadcast delivery from background work.
- [x] Ship the Vue plugin and `mosaicast().on()` / `off()` listener API with an unsubscribe callback and event source.
- [x] Keep channel subscriptions in sync through the always-included `mosaicastSessionIdentifier` Inertia prop.
- [x] Document installation, event objects, Inertia and broadcast delivery, session security, jobs, and Vue integration centrally.

## Release-readiness tests

- [ ] Add a full integration test or repeatable laboratory scenario using configured Reverb, Echo, Inertia, and a real browser session.
- [ ] Test actual HTTP responses for redirects, downloads, streams, and exception paths in addition to direct `RequestHandled` dispatch tests.
- [ ] Test a queued job lifecycle with an explicit target and confirm implicit dispatch fails without a request.
- [ ] Verify history navigation and partial reloads against the real Inertia client so an already-delivered event is never re-emitted unexpectedly.
- [ ] Review session rotation, expiry, cross-origin cookies, and authorization failures in the integration application.

## API decisions

- [ ] Decide whether to add a PHP `mosaicast()` helper. The current PHP entry point is the facade; the JavaScript `mosaicast()` function already exists.
- [ ] Decide whether future releases need user-scoped targets or persisted/replayable delivery. Version 1 currently targets one session and provides best-effort broadcasting.
- [ ] Review the public API and compatibility matrix before the first stable release.
- [ ] Align the PHP `VERSION` constant, JavaScript manifest version, and Git release tags before publishing.

Broadcasting cannot replay an event sent before the browser subscribes. In particular, a partial Inertia response that rotates the session but omits the event prop can send a fallback broadcast before the Vue client joins the new channel. The [delivery guide](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/inertia-delivery/) explains the current mitigation.
