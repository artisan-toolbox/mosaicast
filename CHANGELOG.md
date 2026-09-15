# Release Notes

## Unreleased

- Mosaicast now accepts Laravel event objects, honoring `broadcastAs()` and `broadcastWith()`.
- Added the `DispatchesMosaicast` trait for `Event::mosaicast(...)` dispatching.
- Hardened session-target delivery and client payload validation to prevent malformed channel targets and invalid event data from being processed.
- Refresh the session target after session ID regeneration and leave stale client channels when a session disappears.
- Support string-backed enum event names and configurable Vue channel prefixes; short event-name aliases apply to the default `App\\Events` namespace.
- Run TypeScript type checking as part of the JavaScript package's standard `check` command.
- Keep the Vue channel subscription in sync through an always-included Inertia session identifier, including partial reloads that omit the event prop.
- Consolidated the public documentation, updated the engineering checklist, and replaced the bundled Boost integration placeholder with current adoption guidance.

The initial pre-release has not been tagged yet.
