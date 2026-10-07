<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

it('lists the v1 endpoints with their permission and offers the Postman download', function () {
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->admin()->has(RolePermission::factory()->forModule('example', 'manage')->fullCrud())->create());

    Livewire::actingAs($user)->test('example::integrations.api')
        ->assertSee('/api/v1/example/examples/{id}/status')
        ->assertSee('example.manage.update')
        ->assertSee(__('example::integrations.api_signed'))
        ->call('downloadPostman')
        ->assertFileDownloaded('example-api-v1.postman_collection.json');
});
