<?php

use VmEngine\Example\Models\Example;

describe('Example Module Discovery', function () {
    it('check package module is registered and working', function () {
        // Verify the package is loaded in composer.json
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);

        expect($composerJson['require'] ?? [])
            ->toHaveKey('vm-engine/synmod-example');

        // Verify module models are autoloaded correctly
        expect(class_exists(Example::class))->toBeTrue();

        // Verify route is registered (just check it exists and returns a URL)
        $routeUrl = route('backend.example.index');
        expect($routeUrl)->toBeString()->not->toBeEmpty();
    });
});
