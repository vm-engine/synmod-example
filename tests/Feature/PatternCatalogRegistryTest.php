<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use VmEngine\Example\Catalog\PatternCatalog;

it('has unique keys and only known groups and statuses', function () {
    $patterns = PatternCatalog::patterns();
    $keys = array_column($patterns, 'key');

    expect($keys)->toHaveCount(count(array_unique($keys)));

    foreach ($patterns as $pattern) {
        expect($pattern['group'])->toBeIn(PatternCatalog::GROUPS)
            ->and($pattern['status'])->toBeIn(['built', 'planned'])
            ->and($pattern['subProject'])->toBeBetween(1, 5);
    }
});

it('has at least one pattern in every group', function () {
    $groups = array_unique(array_column(PatternCatalog::patterns(), 'group'));

    expect(array_values(array_diff(PatternCatalog::GROUPS, $groups)))->toBe([]);
});

it('points every built pattern at a real route and real source paths', function () {
    $packageRoot = dirname(__DIR__, 2);

    foreach (array_filter(PatternCatalog::patterns(), fn (array $p): bool => $p['status'] === 'built') as $pattern) {
        expect($pattern['route'])->not->toBeNull()
            ->and(Route::has(PatternCatalog::routeName($pattern['route'])))->toBeTrue("Route missing for {$pattern['key']}")
            ->and($pattern['sources'])->not->toBeEmpty();

        foreach ($pattern['sources'] as $source) {
            expect(file_exists($packageRoot.'/'.$source))->toBeTrue("Missing source {$source} for {$pattern['key']}");
        }
    }
});

it('keeps planned patterns free of routes and sources', function () {
    foreach (array_filter(PatternCatalog::patterns(), fn (array $p): bool => $p['status'] === 'planned') as $pattern) {
        expect($pattern['route'])->toBeNull()
            ->and($pattern['sources'])->toBe([]);
    }
});
