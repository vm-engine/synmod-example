<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('checks permissions in actions through GuardsBackendPermission', function () {
    $offenders = [];

    foreach ((new Finder)->files()->in([__DIR__.'/../../src', __DIR__.'/../../resources/views'])->name('*.php') as $file) {
        if (str_contains($file->getContents(), 'auth()->user()?->can(')) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([]);
});
