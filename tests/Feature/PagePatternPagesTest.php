<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleNode;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    foreach (['manage', 'settings', 'node'] as $feature) {
        RolePermission::factory()->forModule('example', $feature)->fullCrud()->create(['role_id' => $role->id]);
    }
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    Example::factory()->count(3)->create();
    ExampleNode::factory()->create();
});

dataset('page pattern routes', [
    'example.dashboard',
    'example.report',
    'example.report.print',
    'example.progress',
    'example.show',
    'example.settings',
    'example.kanban',
    'example.calendar',
    'example.nodes.browse',
    'example.empty-states',
    'example.components',
]);

it('renders each page pattern', function (string $route) {
    $this->actingAs($this->user)->get(backend_route($route))->assertOk();
})->with('page pattern routes');

it('redirects users without example permissions', function (string $route) {
    $this->actingAs(User::factory()->create())->get(backend_route($route))->assertRedirect();
})->with('page pattern routes');
