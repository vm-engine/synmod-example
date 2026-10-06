<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

it('defaults new examples to draft with a slug from the text', function () {
    $example = Example::factory()->create(['text' => 'Hello World', 'status' => null, 'slug' => null]);

    expect($example->refresh()->status)->toBe(ExampleStatus::Draft)
        ->and($example->slug)->toBe('hello-world');
});

it('keeps slugs unique, including against trashed rows', function () {
    $first = Example::factory()->create(['text' => 'Same Title', 'slug' => null]);
    $first->delete();

    $second = Example::factory()->create(['text' => 'Same Title', 'slug' => null]);

    expect($second->slug)->toBe('same-title-2');
});

it('casts status, meta and due_at', function () {
    $example = Example::factory()->create([
        'status' => ExampleStatus::Review,
        'meta' => ['source' => 'api'],
        'due_at' => '2026-10-20',
    ])->refresh();

    expect($example->status)->toBe(ExampleStatus::Review)
        ->and($example->meta)->toBe(['source' => 'api'])
        ->and($example->due_at->toDateString())->toBe('2026-10-20');
});

it('records the creator on create and the updater on update', function () {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $example = Example::factory()->create();

    expect($example->created_by)->toBe($creator->id)
        ->and($example->updated_by)->toBeNull();

    // HasUpdater only fills updated_by on the updating event.
    $editor = User::factory()->create();
    $this->actingAs($editor);
    $example->update(['text' => 'Edited']);

    expect($example->refresh()->updated_by)->toBe($editor->id)
        ->and($example->created_by)->toBe($creator->id);
});

it('keeps the uploaded file on soft delete and removes it on force delete', function () {
    Storage::fake('public');
    Storage::disk('public')->put('examples/a.txt', 'x');
    $example = Example::factory()->create(['file' => 'examples/a.txt']);

    $example->delete();
    expect($example->trashed())->toBeTrue();
    Storage::disk('public')->assertExists('examples/a.txt');

    $example->forceDelete();
    Storage::disk('public')->assertMissing('examples/a.txt');
});

it('backfills slugs for rows created before the migration', function () {
    $id = DB::table('examples')->insertGetId([
        'text' => 'Legacy Row', 'email' => 'l@example.com', 'protected' => 'x',
        'number' => 1, 'dropdown' => 1, 'slug' => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $migration = require __DIR__.'/../../database/migrations/2026_10_06_000001_add_showcase_columns_to_examples_table.php';
    $migration->backfillSlugs();

    expect(DB::table('examples')->where('id', $id)->value('slug'))->toBe("legacy-row-{$id}");
});
