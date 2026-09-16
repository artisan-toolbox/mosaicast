# Changelog

## [1.0.0] - 2026-09-16

### Features

- **Add Inertia session broadcasting and Vue client integration** (`c8016a1`)
  Introduces Inertia session broadcasting and integrates the Vue client. This is a feature addition intended to improve how session/state updates propagate between the backend and the Vue front-end via Inertia. User impact depends on how consumers use Inertia/Vue in their applications; while this is feature-level behavior, it may require aligning client-side setup or Inertia configuration to ensure the broadcasting and client integration work as intended. No explicit breaking-change notes were provided in the commit summary, but as it affects front-end integration, consumers may need to verify their Inertia/Vue integration after upgrading.

### Fixes

- **Update PHP requirement and clarify engineering checklist** (`9ab8fe1`)
  Updates the documentation to reflect the correct PHP requirement and clarifies items in the engineering checklist. This matters because it ensures contributors and maintainers follow the right prerequisites when preparing changes, reduces onboarding friction, and helps prevent CI failures caused by incorrect environment assumptions. User impact is limited to contributor/maintainer workflow; no runtime package behavior is changed, and no migration steps are required.

### Refactoring

- **Streamline CI workflows and enhance quality checks** (`0337163`)
  Refactors and streamlines CI workflows while enhancing the quality-check coverage. This improves maintainability of the CI configuration and may tighten or reorder quality steps, which can cause newly failing checks to surface earlier in the PR lifecycle. The impact is on contributor CI feedback rather than package runtime behavior; compatibility concerns are limited to ensuring contributors meet the updated lint/quality expectations. No migration steps for users are required.

- **Refactor command attributes, update maintainer config, refine PHPStan/Rector setup and tests** (`b4bdc35`)
  Performs a set of development- and tooling-focused refactors: refactors command attributes, adds maintainer configuration, integrates Rector setup, refines PHPStan configuration, and updates tests accordingly. This matters because it strengthens static analysis and automated refactoring/linting, and keeps tests consistent with the refactor/tooling behavior. User impact is indirect: improved code quality and developer workflow; depending on how tooling enforces rules, contributors may need to adapt to updated standards or updated test expectations. No explicit runtime API changes were described in the commit summary, so no user migration is expected, but maintainers/contributors should be prepared for stricter or altered QA gates.

### Build

- **Initial commit** (`90f005c`)
  Establishes the initial repository state. This commit forms the baseline for all later changes, including CI configuration, tooling, and any initial package structure. User impact is historical; no migration is applicable beyond using the versioned releases built on top of this foundation.

### Continuous Integration

- **Require maintainer ^1.5 in CI quality checks** (`b653829`)
  Adjusts CI quality-check behavior to require `artisan-toolbox/maintainer` at `^1.5`. This ensures the CI pipeline runs with the intended version of the maintainer tooling, improving consistency and reducing the chance of divergent lint/quality results across environments. User impact is on the project’s CI workflow only; it may affect contributors if they run the same commands locally using different maintainer versions. No runtime/package API changes are introduced.

- **Require maintainer in CI before quality checks** (`d51d956`)
  Reworks the CI workflow steps so the maintainer tool is required/installed before executing quality checks. This matters because it makes the pipeline more deterministic: quality checks will use the correct tooling version and be available before linting occurs. User impact is CI execution reliability for contributors; there is no change to application/runtime behavior and no migration needed.

### Maintenance

- **Remove changelog workflow and update dependencies/documentation structure** (`d9d6a6a`)
  Removes the changelog workflow and updates dependencies, while improving the documentation structure. This matters for maintainers because it changes how changelogs are produced and how documentation is organized, likely consolidating release note/changelog generation into the new release configuration. User impact is primarily contributor-facing (release/changelog management), not runtime behavior. Compatibility considerations for users are minimal; however, maintainers should note the workflow removal and rely on the updated documentation/release setup for generating changelogs.

# Release Notes

## Unreleased

- Mosaicast now accepts Laravel event objects, honoring `broadcastAs()` and `broadcastWith()`.
- Added the `DispatchesMosaicast` trait for `Event::mosaicast(...)` dispatching.
- Hardened session-target delivery and client payload validation to prevent malformed channel targets and invalid event data from being processed.
- Refresh the session target after session ID regeneration and leave stale client channels when a session disappears.
- Support string-backed enum event names and configurable Vue channel prefixes; short event-name aliases apply to the default `App\Events` namespace.
- Run TypeScript type checking as part of the JavaScript package's standard `check` command.
- Keep the Vue channel subscription in sync through an always-included Inertia session identifier, including partial reloads that omit the event prop.
- Consolidated the public documentation, updated the engineering checklist, and replaced the bundled Boost integration placeholder with current adoption guidance.
- Install Maintainer 1.5 or newer explicitly in CI before running the configured quality checks, keeping it out of package installations.
