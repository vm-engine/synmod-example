<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use VmEngine\Example\Exports\ExampleExportQuery;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleStats;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-10-07 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('counts examples per status including empty ones', function () {
    Example::factory()->count(2)->draft()->create();
    Example::factory()->published()->create();

    expect(ExampleStats::statusCounts())->toBe(['draft' => 2, 'review' => 0, 'published' => 1]);
});

it('counts overdue and due-this-month examples', function () {
    Example::factory()->draft()->create(['due_at' => '2026-10-01']);
    Example::factory()->published()->create(['due_at' => '2026-10-02']);
    Example::factory()->review()->create(['due_at' => '2026-10-20']);
    Example::factory()->draft()->create(['due_at' => '2026-11-03']);

    expect(ExampleStats::overdueCount())->toBe(1)
        ->and(ExampleStats::dueThisMonthCount())->toBe(3);
});

it('buckets due dates per week starting this week', function () {
    Example::factory()->create(['due_at' => '2026-10-07']);
    Example::factory()->create(['due_at' => '2026-10-11']);
    Example::factory()->create(['due_at' => '2026-10-13']);
    Example::factory()->create(['due_at' => '2026-12-31']);

    $weeks = ExampleStats::duePerWeek(3);

    expect($weeks['labels'])->toBe(['Oct 5', 'Oct 12', 'Oct 19'])
        ->and($weeks['counts'])->toBe([2, 1, 0]);
});

it('builds a status by category matrix with totals for a filtered query', function () {
    $news = ExampleCategory::factory()->create(['name' => 'News']);
    Example::factory()->count(2)->draft()->create(['category_id' => $news->id, 'due_at' => '2026-10-10']);
    Example::factory()->published()->create(['category_id' => null, 'due_at' => '2026-10-11']);
    Example::factory()->published()->create(['category_id' => $news->id, 'due_at' => '2026-12-01']);

    $matrix = ExampleStats::matrix((new ExampleExportQuery(dueTo: '2026-10-31'))());

    expect($matrix['rows'])->toBe([
        ['category' => 'News', 'counts' => ['draft' => 2, 'review' => 0, 'published' => 0], 'total' => 2],
        ['category' => __('example::pages.no_category'), 'counts' => ['draft' => 0, 'review' => 0, 'published' => 1], 'total' => 1],
    ])
        ->and($matrix['totals'])->toBe(['draft' => 2, 'review' => 0, 'published' => 1])
        ->and($matrix['total'])->toBe(3);
});
