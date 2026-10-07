<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleNode;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\Example\Seeders\ExampleCategorySeeder;
use VmEngine\Example\Seeders\ExampleNodeSeeder;
use VmEngine\Example\Seeders\ExampleSeeder;
use VmEngine\Example\Seeders\ExampleTagSeeder;

uses(RefreshDatabase::class);

it('exposes status and due-date factory states', function () {
    expect(Example::factory()->review()->create()->status)->toBe(ExampleStatus::Review);

    $due = Example::factory()->dueThisMonth()->create()->refresh()->due_at;
    expect($due->isSameMonth(CarbonImmutable::now()))->toBeTrue();

    expect(Example::factory()->withTags(3)->create()->tags()->count())->toBe(3);
});

it('seeds tags, examples and a three-level node tree', function () {
    $this->seed([ExampleCategorySeeder::class, ExampleTagSeeder::class, ExampleSeeder::class, ExampleNodeSeeder::class]);

    $dueThisMonth = Example::query()
        ->whereBetween('due_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
        ->count();

    expect(ExampleTag::query()->count())->toBe(8)
        ->and(Example::query()->count())->toBe(60)
        ->and($dueThisMonth)->toBeGreaterThanOrEqual(20)
        ->and(Example::query()->has('tags')->count())->toBe(60)
        ->and(ExampleNode::query()->whereNull('parent_id')->count())->toBe(3)
        ->and(ExampleNode::query()->count())->toBe(30);

    foreach (ExampleStatus::cases() as $status) {
        expect(Example::query()->where('status', $status)->count())->toBe(20);
    }
});

it('does not duplicate tags or nodes when seeded twice', function () {
    $this->seed([ExampleTagSeeder::class, ExampleNodeSeeder::class]);
    $this->seed([ExampleTagSeeder::class, ExampleNodeSeeder::class]);

    expect(ExampleTag::query()->count())->toBe(8)
        ->and(ExampleNode::query()->count())->toBe(30);
});
