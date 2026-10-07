<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'tag')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('renders the tags page', function () {
    $this->actingAs($this->user)->get(backend_route('example.tags'))->assertOk();
});

it('adds a tag from the bottom row', function () {
    Livewire::actingAs($this->user)
        ->test('example::tag-list')
        ->set('newName', 'Release')
        ->set('newColor', '#22c55e')
        ->call('addTag')
        ->assertHasNoErrors()
        ->assertSet('newName', '');

    expect(ExampleTag::query()->where('name', 'Release')->value('color'))->toBe('#22c55e');
});

it('validates name and hex color', function () {
    Livewire::actingAs($this->user)
        ->test('example::tag-list')
        ->set('newName', '')
        ->set('newColor', 'red')
        ->call('addTag')
        ->assertHasErrors(['newName' => 'required', 'newColor' => 'hex_color']);
});

it('edits one row inline', function () {
    $tag = ExampleTag::factory()->create(['name' => 'Old', 'color' => '#000000']);

    Livewire::actingAs($this->user)
        ->test('example::tag-list')
        ->call('startEdit', $tag->id)
        ->assertSet('editingId', $tag->id)
        ->assertSet('editName', 'Old')
        ->set('editName', 'New')
        ->set('editColor', '#3b82f6')
        ->call('saveEdit')
        ->assertSet('editingId', null);

    expect($tag->refresh()->only('name', 'color'))->toBe(['name' => 'New', 'color' => '#3b82f6']);
});

it('detaches a deleted tag from its examples', function () {
    $tag = ExampleTag::factory()->create();
    $example = Example::factory()->create();
    $example->tags()->attach($tag);

    Livewire::actingAs($this->user)
        ->test('example::tag-list')
        ->call('delete', $tag->delete_token)
        ->assertDispatched('notify');

    expect(ExampleTag::query()->find($tag->id))->toBeNull()
        ->and($example->tags()->count())->toBe(0)
        ->and(Example::query()->find($example->id))->not->toBeNull();
});
