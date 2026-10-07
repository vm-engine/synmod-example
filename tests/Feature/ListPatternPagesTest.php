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

dataset('list pattern routes', [
    'example.lists.card-grid',
    'example.lists.bulk',
    'example.lists.trashed',
    'example.lists.sortable',
    'example.lists.grouped',
    'example.lists.expandable',
    'example.lists.load-more',
    'example.lists.inline-filters',
    'example.lists.export',
]);

it('renders each list pattern page with catalog breadcrumbs', function (string $route) {
    $this->actingAs($this->user)
        ->get(backend_route($route))
        ->assertOk()
        ->assertSee(__('example::catalog.title'))
        ->assertSee(__('example::lists.title'));
})->with('list pattern routes');

it('redirects users without example permissions', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(backend_route($route))
        ->assertRedirect();
})->with('list pattern routes');
