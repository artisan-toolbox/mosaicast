<!-- MAINTAINER:README_HEAD:START -->
<div id="maintainer-title" align="center">
    <img src="logo/mosaicast-icon.svg" alt="" width="88">
    <h1>Mosaicast</h1>
</div>

<p id="maintainer-badges" align="center">
<a href="https://github.com/artisan-toolbox/mosaicast/releases"><img src="https://img.shields.io/badge/version-1.1.1-blue?style=flat-square" alt="version"></a>
<a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/php-v/artisan-toolbox/mosaicast.svg?style=flat-square" alt="PHP from Packagist"></a>
<a href="https://github.com/artisan-toolbox/mosaicast/actions"><img src="https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=1.x&amp;label=Tests&amp;style=flat-square" alt="Tests"></a>
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

An Inertia response that resolves the `mosaicast` shared prop carries the event in `mosaicast.events`. Other responses use the current session's private broadcast channel. See the [complete documentation](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/) for jobs, event objects, channel authorization, and the Vue client.

## Resources

- [Documentation](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/)
- [Changelog](CHANGELOG.md)
- [Contributing](.github/CONTRIBUTING.md)
- [Security policy](.github/SECURITY.md)
- [License](LICENSE.md)
