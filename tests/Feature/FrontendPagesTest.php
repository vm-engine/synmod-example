<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

uses(RefreshDatabase::class);

it('lists published examples only, filtered by category, for guests', function () {
    $news = ExampleCategory::factory()->create(['name' => 'News']);
    Example::factory()->published()->create(['text' => 'Public news', 'category_id' => $news->id]);
    Example::factory()->published()->create(['text' => 'Public other']);
    Example::factory()->draft()->create(['text' => 'Secret draft']);

    $this->get(route('example.index'))->assertOk()
        ->assertSee('Public news')->assertSee('Public other')->assertDontSee('Secret draft')
        ->assertSee('<title>'.__('example::frontend.title'), false);

    $this->get(route('example.index', ['category' => $news->id]))->assertSee('Public news')->assertDontSee('Public other');
});

it('shows a published example by slug and 404s otherwise', function () {
    Example::factory()->published()->create(['text' => 'Readable', 'slug' => 'readable', 'content' => '<p>Body text</p>']);
    Example::factory()->review()->create(['slug' => 'in-review']);

    $this->get(route('example.show', 'readable'))->assertOk()->assertSee('Readable')->assertSee('<p>Body text</p>', false);
    $this->get(route('example.show', 'in-review'))->assertNotFound();
    $this->get(route('example.show', 'nope'))->assertNotFound();
});

it('searches title and content with escaped wildcards and highlights matches', function () {
    Example::factory()->published()->create(['text' => 'Alpha launch', 'content' => '<p>rocket</p>']);
    Example::factory()->published()->create(['text' => '100% sure', 'content' => '']);
    Example::factory()->draft()->create(['text' => 'Alpha draft']);

    $this->get(route('example.search', ['q' => 'alpha']))->assertOk()
        ->assertSee('<mark>Alpha</mark> launch', false)->assertDontSee('Alpha draft');
    $this->get(route('example.search', ['q' => 'rocket']))->assertSee('Alpha launch');
    $this->get(route('example.search', ['q' => '%']))->assertSee('100')->assertDontSee('Alpha launch');
});
