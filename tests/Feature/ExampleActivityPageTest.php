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

it('lists example activity, filters by action and embeds the OTP-gated viewer', function () {
    $this->actingAs($this->user);
    $example = Example::factory()->create(['text' => 'Tracked']);
    $example->delete();

    Livewire::test('example::integrations.activity')
        ->assertSee('Created example #'.$example->id)
        ->assertSee('Moved example #'.$example->id)
        ->assertSeeHtml('view-activity-metadata')
        ->set('filterAction', 'example.deleted')
        ->assertDontSee('Created example #'.$example->id)
        ->set('filterAction', 'bogus')
        ->assertSee('Created example #'.$example->id);
});

it('shows the empty state without activity', function () {
    Livewire::actingAs($this->user)->test('example::integrations.activity')
        ->assertSee(__('example::integrations.no_activity'));
});
