<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Support\ExampleSettings;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'settings')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('loads current settings and saves each tab on its own', function () {
    ExampleSettings::save(['wipLimit' => 4]);

    Livewire::actingAs($this->user)
        ->test('example::pages.settings')
        ->assertSet('wipLimit', 4)
        ->set('perPage', 50)
        ->set('showDueColumn', false)
        ->call('saveLists')
        ->assertHasNoErrors()
        ->assertDispatched('notify')
        ->set('defaultStatus', 'review')
        ->set('maxAttachments', 20)
        ->call('saveEditor')
        ->set('wipLimit', 6)
        ->call('saveBoard');

    expect(ExampleSettings::all())->toBe([
        'perPage' => 50,
        'showDueColumn' => false,
        'defaultStatus' => 'review',
        'maxAttachments' => 20,
        'wipLimit' => 6,
    ]);
});

it('validates only the saved tab', function () {
    Livewire::actingAs($this->user)
        ->test('example::pages.settings')
        ->set('perPage', 7)
        ->set('wipLimit', 500)
        ->call('saveLists')
        ->assertHasErrors(['perPage'])
        ->assertHasNoErrors(['wipLimit'])
        ->call('saveBoard')
        ->assertHasErrors(['wipLimit']);

    expect(ExampleSettings::perPage())->toBe(15);
});

it('switches only to known tabs', function () {
    Livewire::actingAs($this->user)
        ->test('example::pages.settings')
        ->call('selectTab', 'board')
        ->assertSet('activeTab', 'board')
        ->call('selectTab', 'nope')
        ->assertSet('activeTab', 'board');
});

it('refuses to save without the settings permission', function () {
    $other = User::factory()->create();
    $other->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'settings')->readOnly())->create());

    Livewire::actingAs($other)
        ->test('example::pages.settings')
        ->set('wipLimit', 9)
        ->call('saveBoard')
        ->assertDispatched('notify');

    expect(ExampleSettings::wipLimit())->toBe(0);
});
