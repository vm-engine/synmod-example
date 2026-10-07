<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\ExampleNode;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'node')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);

    $this->a = ExampleNode::factory()->create(['name' => 'A', 'position' => 0]);
    $this->b = ExampleNode::factory()->create(['name' => 'B', 'position' => 1]);
    $this->a1 = ExampleNode::factory()->childOf($this->a)->create(['name' => 'A1', 'position' => 0]);
    $this->a2 = ExampleNode::factory()->childOf($this->a)->create(['name' => 'A2', 'position' => 1]);
});

it('renders the tree page', function () {
    $this->actingAs($this->user)->get(backend_route('example.nodes'))->assertOk()->assertSee('A1');
});

it('moves a node under another parent and renumbers both sibling lists', function () {
    Livewire::actingAs($this->user)
        ->test('example::node-tree')
        ->call('moveNode', $this->a1->id, 0, (string) $this->b->id);

    expect($this->a1->refresh()->parent_id)->toBe($this->b->id)
        ->and($this->a1->position)->toBe(0)
        ->and($this->a2->refresh()->position)->toBe(0);
});

it('moves a node to the root list', function () {
    Livewire::actingAs($this->user)
        ->test('example::node-tree')
        ->call('moveNode', $this->a2->id, 1, 'root');

    expect($this->a2->refresh()->parent_id)->toBeNull()
        ->and(ExampleNode::query()->roots()->pluck('name')->all())->toBe(['A', 'A2', 'B']);
});

it('refuses to move a node into its own branch', function () {
    Livewire::actingAs($this->user)
        ->test('example::node-tree')
        ->call('moveNode', $this->a->id, 0, (string) $this->a1->id)
        ->assertDispatched('notify');

    expect($this->a->refresh()->parent_id)->toBeNull();
});

it('adds, renames and deletes with the branch', function () {
    $component = Livewire::actingAs($this->user)->test('example::node-tree');

    $component->call('startAdd', (string) $this->b->id)->set('newName', 'B1')->call('addNode');
    expect($this->b->children()->pluck('name')->all())->toBe(['B1']);

    $component->call('startRename', $this->a->id)->set('renameValue', 'Alpha')->call('saveRename');
    expect($this->a->refresh()->name)->toBe('Alpha');

    $component->call('delete', $this->a->delete_token);
    expect(ExampleNode::query()->pluck('name')->all())->toBe(['B', 'B1']);
});

it('validates the node name', function () {
    Livewire::actingAs($this->user)
        ->test('example::node-tree')
        ->call('startAdd', 'root')
        ->set('newName', '')
        ->call('addNode')
        ->assertHasErrors(['newName' => 'required']);
});
