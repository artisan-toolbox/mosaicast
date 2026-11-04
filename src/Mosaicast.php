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
            ->addBadge(Badge::make(
                link: 'https://github.com/artisan-toolbox/mosaicast/releases',
                imgSrc: 'https://img.shields.io/github/v/release/artisan-toolbox/mosaicast?label=Release&style=flat-square',
                imgAlt: 'Release',
            ))
            ->addBadge(Badge::make(
                link: 'https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/#requirements',
                imgSrc: 'https://img.shields.io/badge/PHP-%5E8.5-777BB4?style=flat-square',
                imgAlt: 'PHP',
            ))
            ->addBadge(Badge::make(
                link: 'https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/#requirements',
                imgSrc: 'https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square',
                imgAlt: 'Laravel',
            ))
            ->addBadge(Badge::make(
                link: 'https://github.com/artisan-toolbox/mosaicast/actions/workflows/tests.yml?query=branch%3A1.x',
                imgSrc: 'https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=1.x&label=Tests&style=flat-square',
                imgAlt: 'Tests',
            ))
            ->addBadge(Badge::make(
                link: 'https://artisantoolbox.wsssoftware.com.br/packages/mosaicast/',
                imgSrc: 'https://img.shields.io/badge/Docs-Read-blue?style=flat-square',
                imgAlt: 'Docs',
            ));
    }
}
