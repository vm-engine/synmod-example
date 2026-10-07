<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleAttachment;
use VmEngine\Example\Models\ExampleNode;
use VmEngine\Example\Models\ExampleTag;

uses(RefreshDatabase::class);

it('links examples and tags many-to-many', function () {
    $example = Example::factory()->create();
    $tags = ExampleTag::factory()->count(2)->create();

    $example->tags()->attach($tags);

    expect($example->tags()->count())->toBe(2)
        ->and($tags->first()->examples()->first()->is($example))->toBeTrue();
});

it('generates a tag slug from its name', function () {
    expect(ExampleTag::factory()->create(['name' => 'Live Wire', 'slug' => null])->slug)->toBe('live-wire');
});

it('builds a node tree ordered by position and cascades deletes', function () {
    $root = ExampleNode::factory()->create();
    $second = ExampleNode::factory()->childOf($root)->create(['position' => 1]);
    $first = ExampleNode::factory()->childOf($root)->create(['position' => 0]);
    $leaf = ExampleNode::factory()->childOf($first)->create();

    expect(ExampleNode::query()->roots()->pluck('id')->all())->toBe([$root->id])
        ->and($root->children->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($leaf->parent->is($first))->toBeTrue();

    $root->delete();

    expect(ExampleNode::query()->count())->toBe(0);
});

it('deletes the attachment file with the row', function () {
    Storage::fake('public');
    Storage::disk('public')->put('examples/attachments/a.jpg', 'x');
    $attachment = ExampleAttachment::factory()->create(['path' => 'examples/attachments/a.jpg', 'mime' => 'image/jpeg']);

    expect($attachment->isImage())->toBeTrue()
        ->and($attachment->url())->toContain('examples/attachments/a.jpg');

    $attachment->delete();

    Storage::disk('public')->assertMissing('examples/attachments/a.jpg');
});

it('removes attachment files when the example is force deleted', function () {
    Storage::fake('public');
    Storage::disk('public')->put('examples/attachments/b.pdf', 'x');
    $example = Example::factory()->create();
    ExampleAttachment::factory()->for($example)->create(['path' => 'examples/attachments/b.pdf', 'mime' => 'application/pdf']);

    $example->forceDelete();

    Storage::disk('public')->assertMissing('examples/attachments/b.pdf');
    expect(ExampleAttachment::query()->count())->toBe(0);
});

it('keeps tag slugs unique', function () {
    ExampleTag::factory()->create(['name' => 'Live Wire', 'slug' => null]);
    $second = ExampleTag::factory()->create(['name' => 'Live-Wire', 'slug' => null]);

    expect($second->slug)->toBe('live-wire-2');
});
