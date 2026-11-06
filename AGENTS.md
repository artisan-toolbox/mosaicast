# Mosaicast

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `artisan-toolbox/mosaicast`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.

## Documentation and consumer examples

- Trace a documentation defect to the missing prerequisite, public contract, or shared owner. Fix that source. Do not use copied internals, frontend patches, forced dependencies, or weakened tests to hide it.
- Keep the central legacy and Vue documentation synchronized. Audit the beginner path whenever Mosaicast installation, integration, or examples change. The overview must state purpose, stability, and documented version, then requirements, installation, a working event example with its expected result, and ordered next guides.
- Maintain a dedicated Installation page. Teach a compatible Laravel application, its web session and application key, Composer installation, Composer-delivered frontend dependency, client registration, and build verification in dependency order. Explain broadcaster, private-channel authorization, Echo, and queue prerequisites before examples use them. Distinguish an Inertia response example from the optional private-broadcast setup.
- Define all example inputs and show the request or user action that triggers dispatch. Register the listener before dispatch and state the expected payload, delivery source, and cleanup. A facade call alone is not a complete browser quickstart.
- Consume public package APIs in documentation demos. Keep them under the documentation application's isolated demo paths. Wait for a user start action before starting connections, queues, or jobs, and keep secrets server-side.
- Compare native Laravel or Inertia behavior with Mosaicast using identical payloads, counts, delay, session, and queue conditions. Document observed results and limits without invented performance claims or unrelated infrastructure.
- On documentation tasks, attempt compatible documentation-application updates for Artisan Toolbox and main runtime and test dependencies. Record actual resolved versions and honest conflicts or blockers, run Composer before npm, and verify refreshed frontend copies. Do not change the package's declared compatibility or advertise an unpublished API as released to satisfy that attempt.
- Use installed technical-writing guidance, shared documentation components, editorial menu order, and compact child-page package tags. Keep installed Composer evidence separate from the version documented for consumers.
