<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Mosaicast;

it('keeps the README release badge aligned with the package version', function (): void {
    $head = new Mosaicast()->readmeHead();
    $readme = file_get_contents(dirname(__DIR__, 2).'/README.md');

    expect(Mosaicast::VERSION)->toBeString()
        ->and($readme)->toContain(
            '<!-- MAINTAINER:README_HEAD:START -->',
            'src="logo/mosaicast-icon.svg"',
            $head->badges()[0]->imgSrc(Mosaicast::VERSION),
            '<!-- MAINTAINER:README_HEAD:END -->',
        );
});
