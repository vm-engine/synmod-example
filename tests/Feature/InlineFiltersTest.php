<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
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

it('applies each filter and resets them', function () {
    $category = ExampleCategory::factory()->create();
    Example::factory()->review()->create(['text' => 'Alpha review', 'category_id' => $category->id, 'due_at' => '2026-10-10']);
    Example::factory()->draft()->create(['text' => 'Beta draft', 'due_at' => '2026-11-10']);

    $component = Livewire::actingAs($this->user)->test('example::lists.inline-filters');
    expect($component->get('examples')->total())->toBe(2);

    $component->set('filterStatus', 'review');
    expect($component->get('examples')->total())->toBe(1);

    $component->set('filterStatus', '')->set('filterCategory', (string) $category->id);
    expect($component->get('examples')->total())->toBe(1);

    $component->set('filterCategory', '')->set('dueFrom', '2026-11-01');
    expect($component->get('examples')->first()->text)->toBe('Beta draft');

    $component->set('dueFrom', 'garbage');
    expect($component->get('examples')->total())->toBe(2);

    $component->set('q', 'Alpha')->call('resetFilters')->assertSet('q', '')->assertSet('dueFrom', '');
});

it('keeps filters in the URL', function () {
    Livewire::withQueryParams(['filterStatus' => 'published'])
        ->actingAs($this->user)
        ->test('example::lists.inline-filters')
        ->assertSet('filterStatus', 'published');
});
