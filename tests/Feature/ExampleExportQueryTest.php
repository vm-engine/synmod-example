<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Exports\ExampleExportQuery;
use VmEngine\Example\Exports\ExampleStatusLabel;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

uses(RefreshDatabase::class);

it('filters by search, status, category and due range', function () {
    $category = ExampleCategory::factory()->create();
    $match = Example::factory()->review()->create([
        'text' => 'Quarterly report', 'category_id' => $category->id, 'due_at' => '2026-10-15',
    ]);
    Example::factory()->review()->create(['text' => 'Quarterly other', 'due_at' => '2026-10-15']);
    Example::factory()->draft()->create(['text' => 'Quarterly draft', 'category_id' => $category->id, 'due_at' => '2026-10-15']);
    Example::factory()->review()->create(['text' => 'Quarterly late', 'category_id' => $category->id, 'due_at' => '2026-12-01']);

    $ids = (new ExampleExportQuery(
        search: 'Quarterly', status: 'review', categoryId: $category->id, dueFrom: '2026-10-01', dueTo: '2026-10-31',
    ))()->pluck('id')->all();

    expect($ids)->toBe([$match->id]);
});

it('ignores searches shorter than three characters and returns everything unfiltered', function () {
    Example::factory()->count(3)->create();

    expect((new ExampleExportQuery(search: 'ab'))()->count())->toBe(3);
});

it('turns the status enum into its label for Excel', function () {
    $label = (new ExampleStatusLabel)->transform(ExampleStatus::Published, null);

    expect($label)->toBe('Published')
        ->and((new ExampleStatusLabel)->transform('review', null))->toBe('In review')
        ->and((new ExampleStatusLabel)->transform(null, null))->toBe('');
});
