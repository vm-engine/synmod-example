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
});

it('creates an example from the drawer and asks the drawer to close', function () {
    $category = ExampleCategory::factory()->create();

    Livewire::actingAs($this->user)
        ->test('example::forms.drawer-form')
        ->call('create')
        ->assertDispatched('example-drawer-open')
        ->set('text', 'From drawer')
        ->set('email', 'd@example.com')
        ->set('category_id', $category->id)
        ->set('status', 'review')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('example-drawer-close');

    expect(Example::query()->where('text', 'From drawer')->value('category_id'))->toBe($category->id);
});

it('edits an existing example in the drawer', function () {
    $example = Example::factory()->create(['text' => 'Old']);

    Livewire::actingAs($this->user)
        ->test('example::forms.drawer-form')
        ->call('edit', $example->id)
        ->assertSet('editingId', $example->id)
        ->assertSet('text', 'Old')
        ->set('text', 'New')
        ->call('save');

    expect($example->refresh()->text)->toBe('New');
});

it('validates drawer fields', function () {
    Livewire::actingAs($this->user)
        ->test('example::forms.drawer-form')
        ->call('create')
        ->call('save')
        ->assertHasErrors(['text' => 'required', 'email' => 'required']);
});
