<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\Example\Support\TagResolver;

uses(RefreshDatabase::class);

it('keeps existing ids, matches names case-insensitively and creates the rest', function () {
    $laravel = ExampleTag::factory()->create(['name' => 'Laravel']);
    $php = ExampleTag::factory()->create(['name' => 'PHP']);

    $ids = TagResolver::resolve([$laravel->id, (string) $php->id, 'php', 'Brand New', ' brand new ', '']);

    $new = ExampleTag::query()->where('name', 'Brand New')->first();

    expect($new)->not->toBeNull()
        ->and($ids)->toBe([$laravel->id, $php->id, $new->id])
        ->and(ExampleTag::query()->count())->toBe(3);
});

it('ignores ids that do not exist and overly long names', function () {
    expect(TagResolver::resolve([999, str_repeat('x', 51)]))->toBe([])
        ->and(ExampleTag::query()->count())->toBe(0);
});
