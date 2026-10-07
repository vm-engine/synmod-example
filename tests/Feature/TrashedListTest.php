<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
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
});

it('switches between active and trashed rows', function () {
    Example::factory()->create(['text' => 'Alive']);
    Example::factory()->create(['text' => 'Gone'])->delete();

    $component = Livewire::actingAs($this->user)->test('example::lists.trashed');
    expect($component->get('examples')->pluck('text')->all())->toBe(['Alive']);

    $component->call('setView', 'trash');
    expect($component->get('examples')->pluck('text')->all())->toBe(['Gone']);

    $component->call('setView', 'nonsense')->assertSet('view', 'active');
});

it('restores a trashed example via its token', function () {
    $example = Example::factory()->create();
    $example->delete();

    Livewire::actingAs($this->user)
        ->test('example::lists.trashed')
        ->call('restore', $example->delete_token)
        ->assertDispatched('notify');

    expect($example->refresh()->trashed())->toBeFalse();
});

it('force deletes a trashed example and its file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('examples/x.txt', 'x');
    $example = Example::factory()->create(['file' => 'examples/x.txt']);
    $example->delete();

    Livewire::actingAs($this->user)
        ->test('example::lists.trashed')
        ->call('forceDelete', $example->delete_token);

    expect(Example::withTrashed()->find($example->id))->toBeNull();
    Storage::disk('public')->assertMissing('examples/x.txt');
});

it('refuses an invalid token', function () {
    $example = Example::factory()->create();
    $example->delete();

    Livewire::actingAs($this->user)
        ->test('example::lists.trashed')
        ->call('forceDelete', 'not-a-token')
        ->assertDispatched('notify');

    expect(Example::withTrashed()->find($example->id))->not->toBeNull();
});

it('refuses restore and force delete without the delete permission', function () {
    $example = Example::factory()->create();
    $example->delete();

    $readOnly = User::factory()->create();
    $readOnly->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    Livewire::actingAs($readOnly)
        ->test('example::lists.trashed')
        ->call('restore', $example->delete_token)
        ->call('forceDelete', $example->delete_token)
        ->assertDispatched('notify');

    expect(Example::onlyTrashed()->find($example->id))->not->toBeNull();
});
