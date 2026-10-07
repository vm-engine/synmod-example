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

it('counts validation errors per tab', function () {
    $component = Livewire::actingAs($this->user)
        ->test('example::forms.tabbed')
        ->set('form.color', 'red')
        ->call('addMetaRow')
        ->call('save');

    expect($component->get('tabErrors'))->toBe(['general' => 3, 'content' => 1, 'meta' => 1]);
});

it('switches tabs only to known tabs', function () {
    Livewire::actingAs($this->user)
        ->test('example::forms.tabbed')
        ->call('selectTab', 'content')
        ->assertSet('activeTab', 'content')
        ->call('selectTab', 'nope')
        ->assertSet('activeTab', 'content');
});

it('saves through the shared editor form', function () {
    Livewire::actingAs($this->user)
        ->test('example::forms.tabbed')
        ->set('form.text', 'Tabbed One')
        ->set('form.email', 't@example.com')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Example::query()->where('slug', 'tabbed-one')->exists())->toBeTrue();
});

it('sends the title on blur so the slug can follow it', function () {
    $this->actingAs($this->user)
        ->get(backend_route('example.forms.tabbed'))
        ->assertOk()
        ->assertSee('wire:model.live.blur="form.text"', false);
});
