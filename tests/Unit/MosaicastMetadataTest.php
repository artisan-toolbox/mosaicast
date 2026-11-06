<?php

declare(strict_types=1);

use ArtisanToolbox\Maintainer\Versionable\Readme\Badge;
use ArtisanToolbox\Mosaicast\Mosaicast;

it('publishes the release, backend compatibility, tests, and documentation badges', function (): void {
    $head = new Mosaicast()->readmeHead();
    $readme = file_get_contents(dirname(__DIR__, 2).'/README.md');

    expect(Mosaicast::VERSION)->toBeString()
        ->and($readme)->toContain(
            '<!-- MAINTAINER:README_HEAD:START -->',
            'src="logo/mosaicast-icon.svg"',
            'https://img.shields.io/github/v/release/artisan-toolbox/mosaicast?label=Release&amp;style=flat-square',
            '<!-- MAINTAINER:README_HEAD:END -->',
        )
        ->and(array_map(static fn (Badge $badge): array => [
            $badge->imgAlt(), $badge->link(), $badge->imgSrc(),
        ], $head->badges()))->toBe([
            ['Release', 'https://github.com/artisan-toolbox/mosaicast/releases', 'https://img.shields.io/github/v/release/artisan-toolbox/mosaicast?label=Release&style=flat-square'],
            ['PHP', 'https://atb.dev.br/packages/mosaicast/#requirements', 'https://img.shields.io/badge/PHP-%5E8.5-777BB4?style=flat-square'],
            ['Laravel', 'https://atb.dev.br/packages/mosaicast/#requirements', 'https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square'],
            ['Tests', 'https://github.com/artisan-toolbox/mosaicast/actions/workflows/tests.yml?query=branch%3A1.x', 'https://img.shields.io/github/actions/workflow/status/artisan-toolbox/mosaicast/tests.yml?branch=1.x&label=Tests&style=flat-square'],
            ['Docs', 'https://atb.dev.br/packages/mosaicast/', 'https://img.shields.io/badge/Docs-Read-blue?style=flat-square'],
        ]);
});
