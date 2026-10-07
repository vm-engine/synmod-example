<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('filters the card grid by status chip and counts per status', function () {
    Example::factory()->count(2)->review()->create();
    Example::factory()->published()->create();

    $component = Livewire::actingAs($this->user)->test('example::lists.card-grid');

    expect($component->get('statusCounts'))->toMatchArray(['review' => 2, 'published' => 1, 'draft' => 0]);

    $component->call('setStatus', 'review');
    expect($component->get('examples')->total())->toBe(2);

    $component->call('setStatus', 'bogus')->assertSet('status', 'all');
});

it('groups examples by status in enum order with counts', function () {
    Example::factory()->published()->create(['text' => 'P1']);
    Example::factory()->count(2)->draft()->create();

    $groups = Livewire::actingAs($this->user)->test('example::lists.grouped')->get('groups');

    expect(array_map(fn (array $g): string => $g['status']->value, $groups))->toBe(['draft', 'review', 'published'])
        ->and(array_map(fn (array $g): int => $g['items']->count(), $groups))->toBe([2, 0, 1]);
});

it('expands one row at a time and shows its details', function () {
    $tag = ExampleTag::factory()->create(['name' => 'Pinned']);
    $a = Example::factory()->create(['content' => '<p>Hello <b>world</b></p>', 'meta' => ['source' => 'api']]);
    $a->tags()->attach($tag);
    $b = Example::factory()->create();

    Livewire::actingAs($this->user)
        ->test('example::lists.expandable')
        ->call('toggle', $a->id)
        ->assertSet('expandedId', $a->id)
        ->assertSee('Hello world')
        ->assertDontSeeHtml('<b>world</b>')
        ->assertSee('Pinned')
        ->assertSee('api')
        ->call('toggle', $b->id)
        ->assertSet('expandedId', $b->id)
        ->call('toggle', $b->id)
        ->assertSet('expandedId', null);
});

it('renders the status badge label on the card grid', function () {
    Example::factory()->review()->create(['text' => 'Card one']);

    Livewire::actingAs($this->user)
        ->test('example::lists.card-grid')
        ->assertSee('Card one')
        ->assertSee(ExampleStatus::Review->label());
});
