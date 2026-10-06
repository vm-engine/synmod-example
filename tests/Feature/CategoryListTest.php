<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'category')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('renders the category list page', function () {
    $this->actingAs($this->user)->get(backend_route('example.category'))->assertOk();
});

it('sorts by name and toggles direction', function () {
    ExampleCategory::factory()->create(['name' => 'Beta']);
    ExampleCategory::factory()->create(['name' => 'Alpha']);

    $component = Livewire::actingAs($this->user)
        ->test('example::category-list')
        ->call('sortBy', 'name');

    expect($component->get('categoryList')->first()->name)->toBe('Alpha');

    $component->call('sortBy', 'name');
    expect($component->get('categoryList')->first()->name)->toBe('Beta');
});

it('falls back to id for a non-whitelisted sort field', function () {
    ExampleCategory::factory()->count(2)->create();

    $component = Livewire::actingAs($this->user)
        ->test('example::category-list')
        ->set('sortField', 'description');

    expect($component->get('categoryList')->count())->toBe(2);
});

it('paginates with the per-page selector', function () {
    ExampleCategory::factory()->count(12)->create();

    $component = Livewire::actingAs($this->user)
        ->test('example::category-list')
        ->set('perPage', 10);

    expect($component->get('categoryList')->perPage())->toBe(10);
});

it('deletes a category with its delete token', function () {
    $category = ExampleCategory::factory()->create();

    Livewire::actingAs($this->user)
        ->test('example::category-list')
        ->call('delete', $category->delete_token)
        ->assertDispatched('notify');

    $this->assertDatabaseMissing('example_categories', ['id' => $category->id]);
});
