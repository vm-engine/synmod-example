<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
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

it('loads, validates and saves an example in the child form', function () {
    $example = Example::factory()->draft()->create(['text' => 'Before']);

    Livewire::actingAs($this->user)
        ->test('example::forms.quick-edit')
        ->dispatch('quick-edit-load', id: $example->id)
        ->assertSet('exampleId', $example->id)
        ->assertSet('text', 'Before')
        ->set('text', '')
        ->call('save')
        ->assertHasErrors(['text' => 'required'])
        ->set('text', 'After')
        ->set('status', 'published')
        ->call('save')
        ->assertDispatched('example-saved')
        ->assertDispatched('close-modal-quick-edit');

    expect($example->refresh()->only('text'))->toBe(['text' => 'After'])
        ->and($example->status)->toBe(ExampleStatus::Published);
});

it('closes with an error when the example is gone', function () {
    Livewire::actingAs($this->user)
        ->test('example::forms.quick-edit')
        ->dispatch('quick-edit-load', id: 999)
        ->assertDispatched('notify')
        ->assertDispatched('close-modal-quick-edit');
});

it('refreshes the parent list when the child saves', function () {
    Example::factory()->create(['text' => 'Row one']);

    Livewire::actingAs($this->user)
        ->test('example::forms.modal-child')
        ->assertSee('Row one')
        ->dispatch('example-saved', id: 1)
        ->assertOk();
});
