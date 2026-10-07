<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
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

it('renders the partial with a link or a click action', function () {
    $link = Blade::render("@include('example::partials.empty-state', ['icon' => 'ph ph-x', 'title' => 'T', 'text' => 'Body', 'action' => ['label' => 'Go', 'href' => '/go']])");
    $click = Blade::render("@include('example::partials.empty-state', ['icon' => 'ph ph-x', 'title' => 'T', 'text' => null, 'action' => ['label' => 'Clear', 'click' => 'clearFilters']])");

    expect($link)->toContain('href="/go"')->toContain('Body')
        ->and($click)->toContain('wire:click="clearFilters"');
});

it('shows all three variants on the empty states page', function () {
    Livewire::actingAs($this->user)
        ->test('example::pages.empty-states')
        ->assertSee(__('example::pages.first_run'))
        ->assertSee(__('example::pages.no_results'))
        ->assertSee(__('example::pages.error_state'));
});

it('uses the first-run and no-results states in the example list', function () {
    Livewire::actingAs($this->user)->test('example::example-list')->assertSee(__('example::pages.no_examples'));

    Example::factory()->create(['text' => 'Present']);

    Livewire::actingAs($this->user)
        ->test('example::example-list')
        ->set('q', 'zzzz-nothing')
        ->assertSee(__('example::pages.no_results'))
        ->call('clearFilters')
        ->assertSet('q', null)
        ->assertSee('Present');
});
