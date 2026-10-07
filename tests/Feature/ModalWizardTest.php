<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

it('gates each step on its own fields and creates on finish', function () {
    $component = Livewire::actingAs($this->user)
        ->test('example::forms.modal-wizard')
        ->call('next')
        ->assertHasErrors(['text' => 'required'])
        ->assertSet('step', 1)
        ->set('text', 'Wizard made')
        ->set('email', 'w@example.com')
        ->call('next')
        ->assertSet('step', 2)
        ->set('status', 'review')
        ->set('due_at', 'nope')
        ->call('next')
        ->assertHasErrors(['due_at'])
        ->set('due_at', '2026-11-20')
        ->call('next')
        ->assertSet('step', 3)
        ->call('goToStep', 1)
        ->assertSet('step', 1)
        ->call('goToStep', 3)
        ->assertSet('step', 1);

    $component->call('next')->call('next')->call('finish')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-example-wizard')
        ->assertSet('step', 1)
        ->assertSet('text', '');

    expect(Example::query()->where('text', 'Wizard made')->value('due_at')?->toDateString())->toBe('2026-11-20');
});
