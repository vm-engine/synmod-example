<?php

use Illuminate\Support\Facades\App;
use VmEngine\Example\ExampleServiceProvider;

describe('Example Module Discovery', function () {
    it('check module registered in composer.json', function () {
        expect(stripslashes(file_get_contents(base_path('composer.json'))))
            ->toContain(
                'SynApps\\Modules\\Example',
                'SynDB\\Modules\\Example'
            );
        expect(App::providerIsLoaded(ExampleServiceProvider::class))->toBeTrue();
    });
});
