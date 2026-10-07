<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);

    $this->category = ExampleCategory::factory()->create(['is_active' => true]);
    $this->items = collect(range(0, 3))->map(fn (int $i) => Example::factory()->create([
        'category_id' => $this->category->id, 'position' => $i, 'text' => "Item {$i}",
    ]));
});

it('defaults to the first active category and lists by position', function () {
    $component = Livewire::actingAs($this->user)->test('example::lists.sortable');

    expect($component->get('categoryId'))->toBe($this->category->id)
        ->and($component->get('items')->pluck('text')->all())->toBe(['Item 0', 'Item 1', 'Item 2', 'Item 3']);
});

it('moves an item and renumbers positions', function () {
    Livewire::actingAs($this->user)
        ->test('example::lists.sortable')
        ->call('moveItem', $this->items[3]->id, 0);

    expect(Example::query()->where('category_id', $this->category->id)->orderBy('position')->pluck('text')->all())
        ->toBe(['Item 3', 'Item 0', 'Item 1', 'Item 2'])
        ->and(Example::query()->where('category_id', $this->category->id)->orderBy('position')->pluck('position')->all())
        ->toBe([0, 1, 2, 3]);
});

it('ignores an example from another category', function () {
    $stranger = Example::factory()->create(['position' => 9]);

    Livewire::actingAs($this->user)
        ->test('example::lists.sortable')
        ->set('categoryId', $this->category->id)
        ->call('moveItem', $stranger->id, 0)
        ->assertDispatched('notify');

    expect($stranger->refresh()->position)->toBe(9);
});

it('refuses to reorder without the update permission', function () {
    $readOnly = User::factory()->create();
    $readOnly->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    Livewire::actingAs($readOnly)
        ->test('example::lists.sortable')
        ->set('categoryId', $this->category->id)
        ->call('moveItem', $this->items[3]->id, 0)
        ->assertDispatched('notify');

    expect($this->items[3]->refresh()->position)->toBe(3);
});
