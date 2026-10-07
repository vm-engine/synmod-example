<?php

declare(strict_types=1);

/*
 * Convention: date fields use <x-synapse-datepicker>, never a native date input.
 */
it('uses the synapse datepicker instead of native date inputs', function () {
    $offenders = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/views'));

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php') && str_contains((string) file_get_contents($file->getPathname()), 'type="date"')) {
            $offenders[] = str_replace(dirname(__DIR__, 2).'/', '', $file->getPathname());
        }
    }

    expect($offenders)->toBe([]);
});
