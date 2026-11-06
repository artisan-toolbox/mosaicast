<!-- MAINTAINER:README_HEAD:START -->
<div id="maintainer-title" align="center">
    <img src="logo/mosaicast-icon.svg" alt="" width="88">
    <h1>Mosaicast</h1>
</div>

<p id="maintainer-badges" align="center">
<a href="https://github.com/artisan-toolbox/mosaicast/releases"><img src="https://img.shields.io/github/v/release/artisan-toolbox/mosaicast?label=Release&amp;style=flat-square" alt="Release"></a>
<a href="https://atb.dev.br/packages/mosaicast/#requirements"><img src="https://img.shields.io/badge/PHP-%5E8.5-777BB4?style=flat-square" alt="PHP"></a>
<a href="https://atb.dev.br/packages/mosaicast/#requirements"><img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square" alt="Laravel"></a>
<a href="https://github.com/artisan-toolbox/mosaicast/actions/workflows/tests.yml?query=branch%3A1.x"><img src="https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=1.x&amp;label=Tests&amp;style=flat-square" alt="Tests"></a>
<a href="https://atb.dev.br/packages/mosaicast/"><img src="https://img.shields.io/badge/Docs-Read-blue?style=flat-square" alt="Docs"></a>
</p>

<p id="maintainer-description" align="center">
    Laravel event delivery through Inertia props or private session broadcasting.
</p>
<!-- MAINTAINER:README_HEAD:END -->

## Installation

You can install the package via Composer:

```bash
composer require artisan-toolbox/mosaicast
```

Requires PHP 8.5 and Laravel 13.

## Usage

Dispatch an event during a request:

```php
use ArtisanToolbox\Mosaicast\Facades\Mosaicast;

Mosaicast::dispatch('orders.updated', [
    'orderId' => $order->id,
]);
```

The `mosaicast()` helper offers the same implicit dispatch API. Call it without
an event to target a captured session explicitly: `mosaicast()->toSession($identifier)->dispatch(...)`.

An Inertia response that resolves the `mosaicast` shared prop carries the event in `mosaicast.events`. Other responses use the current session's private broadcast channel. See the [complete documentation](https://atb.dev.br/packages/mosaicast/) for jobs, event objects, channel authorization, and the Vue client.

## Resources

- [Documentation](https://atb.dev.br/packages/mosaicast/)
- [Changelog](CHANGELOG.md)
- [Contributing](.github/CONTRIBUTING.md)
- [Security policy](.github/SECURITY.md)
- [License](LICENSE.md)
