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
    foreach (['manage', 'category', 'tag', 'node'] as $feature) {
        RolePermission::factory()->forModule('example', $feature)->fullCrud()->create(['role_id' => $role->id]);
    }
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

/*
 * In the browser, $wire resolves a public property before a public method of
 * the same name, so wire:click="foo" silently reads $foo instead of calling
 * foo(). Server-side tests call the method directly and never notice.
 */
it('has no public method that shares a name with a public property', function (string $component) {
    $instance = Livewire::actingAs($this->user)->test($component)->instance();
    $reflection = new ReflectionObject($instance);

    $properties = array_map(fn (ReflectionProperty $p): string => $p->getName(), $reflection->getProperties(ReflectionProperty::IS_PUBLIC));
    $methods = array_map(fn (ReflectionMethod $m): string => $m->getName(), $reflection->getMethods(ReflectionMethod::IS_PUBLIC));

    expect(array_values(array_intersect($properties, $methods)))->toBe([]);
})->with([
    'example::example-list',
    'example::category-list',
    'example::pattern-catalog',
    'example::tag-list',
    'example::node-tree',
    'example::lists.card-grid',
    'example::lists.bulk-actions',
    'example::lists.trashed',
    'example::lists.sortable',
    'example::lists.grouped',
    'example::lists.expandable',
    'example::lists.load-more',
    'example::lists.inline-filters',
    'example::lists.export',
]);
