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
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'node')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('renders the tree and edits the selected node', function () {
    $root = ExampleNode::factory()->create(['name' => 'Root', 'parent_id' => null]);
    $child = ExampleNode::factory()->create(['name' => 'Leaf', 'parent_id' => $root->id]);

    Livewire::actingAs($this->user)
        ->test('example::pages.node-browser')
        ->assertSee('Root')
        ->assertSee('Leaf')
        ->assertSee(__('example::pages.select_node'))
        ->call('selectNode', $child->id)
        ->assertSet('node', $child->id)
        ->assertSet('name', 'Leaf')
        ->set('name', 'Renamed leaf')
        ->set('description', 'Now described')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect($child->refresh()->only('name', 'description'))->toBe(['name' => 'Renamed leaf', 'description' => 'Now described']);
});

it('shows the path and children of the selected node', function () {
    $root = ExampleNode::factory()->create(['name' => 'Root', 'parent_id' => null]);
    $mid = ExampleNode::factory()->create(['name' => 'Middle', 'parent_id' => $root->id]);
    ExampleNode::factory()->create(['name' => 'Bottom', 'parent_id' => $mid->id]);

    $component = Livewire::withQueryParams(['node' => $mid->id])->actingAs($this->user)
        ->test('example::pages.node-browser')
        ->assertSet('name', 'Middle');

    expect(array_column($component->instance()->path(), 'name'))->toBe(['Root', 'Middle']);
});

it('falls back to the empty state for an unknown node and validates', function () {
    $node = ExampleNode::factory()->create(['name' => 'Only']);

    Livewire::withQueryParams(['node' => 999])->actingAs($this->user)
        ->test('example::pages.node-browser')
        ->assertSet('node', null)
        ->call('selectNode', $node->id)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
