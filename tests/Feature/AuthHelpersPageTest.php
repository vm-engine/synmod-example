<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

it('reports helper results for the current user and guards the demo action', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::factory()->admin()->has(RolePermission::factory()->forModule('example', 'manage')->fullCrud())->create());

    $reader = User::factory()->create();
    $reader->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    $adminPage = Livewire::actingAs($admin)->test('example::integrations.auth-helpers');
    expect(collect($adminPage->instance()->helperResults())->pluck('result', 'call')->all())
        ->toMatchArray(["can_access('example.manage.delete')" => true, "has_role('admin')" => true, 'is_dev()' => false]);
    $adminPage->call('guardedDemo')->assertDispatched('notify', variant: 'success');

    $readerPage = Livewire::actingAs($reader)->test('example::integrations.auth-helpers');
    expect(collect($readerPage->instance()->helperResults())->pluck('result', 'call')->all())
        ->toMatchArray(["can_access('example.manage.delete')" => false, "has_role('admin')" => false]);
    $readerPage->call('guardedDemo')->assertDispatched('notify', variant: 'danger')
        ->assertSee("@canAccess('example.manage.delete')");
});
