<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

const EXAMPLE_JS_IMPORT = "import '../../vendor/vm-engine/synmod-example/resources/js/example.js';";

// Fixtures live in a temp base path so the real host files are never touched.
beforeEach(function () {
    $this->basePath = sys_get_temp_dir().'/example-setup-test-'.uniqid();
    mkdir($this->basePath.'/resources/js', 0755, true);
    $this->appJs = $this->basePath.'/resources/js/app.js';
    file_put_contents($this->appJs, "import '../../synapps/resources/js/synapps-app.js';\n");
    $this->realBasePath = $this->app->basePath();
    $this->app->setBasePath($this->basePath);
});

afterEach(function () {
    $this->app->setBasePath($this->realBasePath);
    File::deleteDirectory($this->basePath);
});

it('adds the example.js import to app.js once', function () {
    $this->artisan('example:setup')->assertSuccessful();
    $this->artisan('example:setup')->assertSuccessful();

    expect(substr_count((string) file_get_contents($this->appJs), EXAMPLE_JS_IMPORT))->toBe(1);
});

it('warns when app.js is missing', function () {
    unlink($this->appJs);

    $this->artisan('example:setup')
        ->expectsOutputToContain('app.js not found')
        ->assertSuccessful();
});
