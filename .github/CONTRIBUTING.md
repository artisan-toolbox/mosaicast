# Contribution Guide

Thank you for considering contributing to Mosaicast! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Setup

Use PHP 8.5 and Node.js 24.11 or later within the Node.js 24 series, matching CI. These Node.js requirements apply to the package's development toolchain. Applications install the compiled client from the Composer package.

Clone your fork, then install the development dependencies from the package root, with Composer before npm:

```bash
composer install
npm ci
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```

Run the configured CI checks and build the distributed frontend assets:

```bash
php vendor/bin/maintainer quality:check --no-interaction
npm run build
```

The checks validate PHP tests, formatting, static analysis, frontend tests, and TypeScript. The build writes the ESM client, IIFE bundle, and type declarations to `dist/`.

When updating Vite+, keep its version, the `overrides.vite` core alias, and the `overrides.vitest` version aligned with the versions bundled by Vite+. Follow the [Vite+ update guide](https://viteplus.dev/guide/upgrade-project) and commit the refreshed lockfile and `dist/` assets together.
