<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

dataset('integration routes', [
    'example.integrations.auth-helpers',
    'example.integrations.activity',
    'example.integrations.abac',
    'example.integrations.api',
    'example.integrations.notifications',
    'example.integrations.import',
]);

it('renders each integration page', function (string $route) {
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->admin()->has(RolePermission::factory()->forModule('example', 'manage')->fullCrud())->create());

    $this->actingAs($user)->get(backend_route($route))->assertOk();
})->with('integration routes');

it('redirects users without example permissions', function (string $route) {
    $this->actingAs(User::factory()->create())->get(backend_route($route))->assertRedirect();
})->with('integration routes');
