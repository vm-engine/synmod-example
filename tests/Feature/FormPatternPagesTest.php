<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    Example::factory()->count(3)->create();
});

dataset('form pattern routes', [
    'example.editor',
    'example.forms.modal-child',
    'example.forms.drawer',
    'example.forms.tabbed',
    'example.forms.modal-wizard',
    'example.forms.page-wizard',
]);

it('renders each form pattern page with catalog breadcrumbs', function (string $route) {
    $this->actingAs($this->user)
        ->get(backend_route($route))
        ->assertOk()
        ->assertSee(__('example::catalog.title'))
        ->assertSee(__('example::forms.title'));
})->with('form pattern routes');

it('redirects users without example permissions', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(backend_route($route))
        ->assertRedirect();
})->with('form pattern routes');

it('opens the editor for an existing example', function () {
    $example = Example::query()->first();

    $this->actingAs($this->user)
        ->get(backend_route('example.editor', ['id' => $example->id]))
        ->assertOk()
        ->assertSee($example->slug);
});
