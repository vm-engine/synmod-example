<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

dataset('example lang files', fn (): array => array_map(basename(...), glob(__DIR__.'/../../lang/en/*.php') ?: []));

it('has an Indonesian line for every English key with the same placeholders', function (string $file) {
    $idPath = __DIR__.'/../../lang/id/'.$file;

    expect($idPath)->toBeFile();

    $en = Arr::dot(require __DIR__.'/../../lang/en/'.$file);
    $id = Arr::dot(require $idPath);

    expect(array_keys($id))->toEqualCanonicalizing(array_keys($en));

    $placeholders = function (string $line): array {
        preg_match_all('/:[a-z_]+/', $line, $matches);
        sort($matches[0]);

        return $matches[0];
    };

    foreach ($en as $key => $line) {
        expect($placeholders($id[$key]))->toBe($placeholders($line), "{$file}: {$key}");
    }
})->with('example lang files');
