<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('loads twelve more at a time and stops at the end', function () {
    Example::factory()->count(30)->create();

    $component = Livewire::actingAs($this->user)->test('example::lists.load-more');
    expect($component->get('examples'))->toHaveCount(12)
        ->and($component->get('hasMore'))->toBeTrue();

    $component->call('loadMore')->call('loadMore');
    expect($component->get('examples'))->toHaveCount(30)
        ->and($component->get('hasMore'))->toBeFalse()
        ->and($component->get('limit'))->toBe(36);

    $component->call('loadMore')->assertSet('limit', 36)
        ->assertSee(__('example::lists.end_of_list'));
});
