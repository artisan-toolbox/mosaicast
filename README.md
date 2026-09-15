<div align="center">
    <h1>Mosaicast</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/v/artisan-toolbox/mosaicast.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/php-v/artisan-toolbox/mosaicast.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://badge.laravel.cloud/badge/artisan-toolbox/mosaicast?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/artisan-toolbox/mosaicast/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/artisan-toolbox/mosaicast"><img src="https://img.shields.io/packagist/dt/artisan-toolbox/mosaicast.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Mosaicast is a Laravel event delivery package that intelligently routes events through the channel best suited to the current context.

## Installation

You can install the package via Composer:

```bash
composer require artisan-toolbox/mosaicast
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="mosaicast"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="mosaicast-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="mosaicast-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="mosaicast-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="mosaicast-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="mosaicast-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Mosaicast! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Allan Mariucci Carvalho](https://github.com/artisan-toolbox)
- [All Contributors](../../contributors)

## License

Mosaicast is open-sourced software licensed under the [MIT license](LICENSE.md).
