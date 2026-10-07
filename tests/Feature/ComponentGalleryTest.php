<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('renders every gallery section', function () {
    Livewire::actingAs($this->user)
        ->test('example::pages.component-gallery')
        ->assertSee(__('example::components.alerts'))
        ->assertSee(__('example::components.badges'))
        ->assertSee(__('example::components.panels'))
        ->assertSee(__('example::components.stat_tiles'))
        ->assertSee(__('example::components.overlays'));
});

it('toasts only whitelisted variants and runs the confirmed action', function () {
    Livewire::actingAs($this->user)
        ->test('example::pages.component-gallery')
        ->call('toast', 'success')
        ->assertDispatched('notify', variant: 'success')
        ->call('toast', 'nope')
        ->assertDispatched('notify', variant: 'info')
        ->call('confirmedDemo')
        ->assertDispatched('notify', message: __('example::components.confirmed'));
});
