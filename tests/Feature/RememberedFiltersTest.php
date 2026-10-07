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
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('restores the example list search and sort on return', function () {
    Livewire::actingAs($this->user)->test('example::example-list')
        ->set('q', 'remembered')
        ->call('sortBy', 'text');

    Livewire::actingAs($this->user)->test('example::example-list')
        ->assertSet('q', 'remembered')
        ->assertSet('sortField', 'text');
});

it('lets the URL win over the remembered value', function () {
    Livewire::actingAs($this->user)->test('example::lists.inline-filters')->set('filterStatus', 'review');

    Livewire::actingAs($this->user)->test('example::lists.inline-filters')->assertSet('filterStatus', 'review');

    Livewire::withQueryParams(['filterStatus' => 'draft'])->actingAs($this->user)
        ->test('example::lists.inline-filters')
        ->assertSet('filterStatus', 'draft');
});
