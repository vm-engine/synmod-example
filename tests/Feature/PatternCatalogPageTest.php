<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Catalog\PatternCatalog;
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

it('renders for a user with any example permission', function () {
    $this->actingAs($this->user)
        ->get(backend_route('example.catalog'))
        ->assertOk()
        ->assertSee('Pattern Catalog')
        ->assertSee('Table list')
        ->assertSee('Kanban board');
});

it('redirects a user without example permissions', function () {
    $this->actingAs(User::factory()->create())
        ->get(backend_route('example.catalog'))
        ->assertRedirect();
});

it('reports build progress', function () {
    $built = count(array_filter(PatternCatalog::patterns(), fn (array $p): bool => $p['status'] === 'built'));

    Livewire::actingAs($this->user)
        ->test('example::pattern-catalog')
        ->assertSet('q', '')
        ->assertSee($built.' / '.count(PatternCatalog::patterns()).' built');
});

it('filters by search text, group and built-only', function () {
    $component = Livewire::actingAs($this->user)->test('example::pattern-catalog');

    $component->set('q', 'kanban');
    expect(array_column($component->get('patterns'), 'key'))->toBe(['page-kanban']);

    $component->set('q', '')->call('setGroup', 'forms');
    expect(array_values(array_unique(array_column($component->get('patterns'), 'group'))))->toBe(['forms']);

    $component->call('setGroup', 'all')->set('builtOnly', true);
    expect(array_values(array_unique(array_column($component->get('patterns'), 'status'))))->toBe(['built']);
});

it('ignores an unknown group', function () {
    Livewire::actingAs($this->user)
        ->test('example::pattern-catalog')
        ->call('setGroup', 'nope')
        ->assertSet('group', 'all');
});

it('shows the empty state when nothing matches', function () {
    Livewire::actingAs($this->user)
        ->test('example::pattern-catalog')
        ->set('q', 'zzzz-no-such-pattern')
        ->assertSee(__('example::catalog.empty'));
});

it('renders copy buttons for built source paths', function () {
    Livewire::actingAs($this->user)
        ->test('example::pattern-catalog')
        ->assertSeeHtml('synapseCopyButton(&quot;resources\/views\/components\/example-list&quot;');
});
