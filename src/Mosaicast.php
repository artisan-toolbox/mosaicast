<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast;

use ArtisanToolbox\Maintainer\Versionable\Contracts\Versionable;
use ArtisanToolbox\Maintainer\Versionable\Contracts\WithReadmeHead;
use ArtisanToolbox\Maintainer\Versionable\Readme\Badge;
use ArtisanToolbox\Maintainer\Versionable\Readme\Head;

final class Mosaicast implements Versionable, WithReadmeHead
{
    public const string VERSION = '1.1.1';

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
