<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Synapse\Rte\RteSetup;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

/** The editor page uses the optional Synapse rich-text editor. */
beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('renders the Synapse editor bound to the content field when the editor is enabled', function () {
    config(['synapps.apps.rte.enabled' => true]);

    Livewire::actingAs($this->user)->test('example::example-editor')
        ->assertSeeHtml('x-data="synapseRte(')
        ->assertSeeHtml('&quot;field&quot;:&quot;form.content&quot;')
        ->assertDontSee(RteSetup::DISABLED_MESSAGE);
});

it('shows the disabled notice above a plain bound textarea when the editor is disabled', function () {
    config(['synapps.apps.rte.enabled' => false]);

    Livewire::actingAs($this->user)->test('example::example-editor')
        ->assertSee(RteSetup::DISABLED_MESSAGE)
        ->assertSeeHtml('wire:model="form.content"')
        ->assertDontSeeHtml('synapseRte(');
});
