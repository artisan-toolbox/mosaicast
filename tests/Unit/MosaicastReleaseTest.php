<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\MosaicastRelease;

it('keeps the README release badge aligned with the runtime version', function (): void {
    $head = new MosaicastRelease()->readmeHead();
    $readme = file_get_contents(dirname(__DIR__, 2).'/README.md');

    expect(MosaicastRelease::VERSION)->toBeString()
        ->and($readme)->toContain(
            '<!-- MAINTAINER:README_HEAD:START -->',
            'src="logo/mosaicast-icon.svg"',
            $head->badges()[0]->imgSrc(MosaicastRelease::VERSION),
            '<!-- MAINTAINER:README_HEAD:END -->',
        );
});
