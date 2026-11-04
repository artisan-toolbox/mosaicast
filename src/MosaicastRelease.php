<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast;

use ArtisanToolbox\Maintainer\Versionable\Contracts\BeforeVersioning;
use ArtisanToolbox\Maintainer\Versionable\Contracts\Versionable;
use ArtisanToolbox\Maintainer\Versionable\Contracts\WithReadmeHead;
use ArtisanToolbox\Maintainer\Versionable\Readme\Badge;
use ArtisanToolbox\Maintainer\Versionable\Readme\Head;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class MosaicastRelease implements BeforeVersioning, Versionable, WithReadmeHead
{
    public const string VERSION = '1.1.1';

    public static function beforeVersioning(string $current, string $next): void
    {
        $files = new Filesystem;
        $path = __DIR__.'/Mosaicast.php';
        $source = $files->get($path);
        $updated = str_replace(
            "public const string VERSION = '{$current}';",
            "public const string VERSION = '{$next}';",
            $source,
            $replacements,
        );

        throw_unless($replacements === 1, RuntimeException::class, 'The Mosaicast runtime version must match the current release version.');

        $files->replace($path, $updated);
    }

    public function readmeHead(): Head
    {
        return (new Head)
            ->withLogo(src: 'logo/mosaicast-icon.svg', alt: '', width: 88)
            ->withTitle('Mosaicast')
            ->withDescription('Laravel event delivery through Inertia props or private session broadcasting.')
            ->addBadge(Badge::releaseVersion('https://github.com/artisan-toolbox/mosaicast/releases'))
            ->addBadge(Badge::make(
                link: 'https://packagist.org/packages/artisan-toolbox/mosaicast',
                imgSrc: 'https://img.shields.io/packagist/php-v/artisan-toolbox/mosaicast.svg?style=flat-square',
                imgAlt: 'PHP from Packagist',
            ))
            ->addBadge(Badge::make(
                link: 'https://github.com/artisan-toolbox/mosaicast/actions',
                imgSrc: 'https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=1.x&label=Tests&style=flat-square',
                imgAlt: 'Tests',
            ));
    }
}
