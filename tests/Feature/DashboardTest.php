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
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('shows stat tiles and both charts', function () {
    Example::factory()->count(3)->published()->create();

    Livewire::actingAs($this->user)
        ->test('example::pages.dashboard')
        ->assertSee(__('example::pages.total'))
        ->assertSee(__('example::pages.overdue'))
        ->assertSeeHtml('@example-chart-status.window')
        ->assertSeeHtml('lazy');
});

it('renders the lazy panels with their items', function () {
    $recent = Example::factory()->create(['text' => 'Freshly touched']);
    Example::factory()->create(['text' => 'Due very soon', 'due_at' => today()->addDays(3)]);

    Livewire::actingAs($this->user)->test('example::pages.dashboard-panel', ['kind' => 'recent'])->assertSee('Freshly touched');
    Livewire::actingAs($this->user)->test('example::pages.dashboard-panel', ['kind' => 'due-soon'])->assertSee('Due very soon');
    Livewire::actingAs($this->user)->test('example::pages.dashboard-panel', ['kind' => 'bogus'])->assertSet('kind', 'recent');
});
