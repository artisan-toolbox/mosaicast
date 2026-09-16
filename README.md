<div align="center">
    <h1>Mosaicast</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/v/artisan-toolbox/mosaicast.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/php-v/artisan-toolbox/mosaicast.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://github.com/artisan-toolbox/mosaicast/actions"><img src="https://github.com/artisan-toolbox/mosaicast/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
</p>

<p align="center">
    Laravel event delivery through Inertia props or private session broadcasting.
</p>

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

An Inertia response that resolves the `mosaicast` shared prop carries the event in `mosaicast.events`. Other responses use the current session's private broadcast channel. See the [complete documentation](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/) for jobs, event objects, channel authorization, and the Vue client.

## Resources

- [Documentation](https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/)
- [Changelog](CHANGELOG.md)
- [Contributing](.github/CONTRIBUTING.md)
- [Security policy](.github/SECURITY.md)
- [License](LICENSE.md)
