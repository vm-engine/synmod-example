<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleSettings;
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

it('moves a card to another column at a position', function () {
    $a = Example::factory()->review()->create(['position' => 0]);
    $b = Example::factory()->review()->create(['position' => 1]);
    $moving = Example::factory()->draft()->create();

    Livewire::actingAs($this->user)
        ->test('example::pages.kanban')
        ->call('moveItem', $moving->id, 1, 'review')
        ->assertDispatched('notify');

    expect($moving->refresh()->status)->toBe(ExampleStatus::Review)
        ->and(Example::query()->where('status', 'review')->orderBy('position')->pluck('id')->all())->toBe([$a->id, $moving->id, $b->id]);
});

it('asks for a publish note before moving to published', function () {
    $moving = Example::factory()->draft()->create(['meta' => null]);

    $component = Livewire::actingAs($this->user)
        ->test('example::pages.kanban')
        ->call('moveItem', $moving->id, 0, 'published')
        ->assertDispatched('open-modal-publish-note')
        ->assertSet('pendingMoveId', $moving->id);

    expect($moving->refresh()->status)->toBe(ExampleStatus::Draft);

    $component->call('confirmPublish')->assertHasErrors(['publishNote' => 'required'])
        ->set('publishNote', 'Ship it')
        ->call('confirmPublish')
        ->assertDispatched('close-modal-publish-note')
        ->assertSet('pendingMoveId', null);

    expect($moving->refresh()->status)->toBe(ExampleStatus::Published)
        ->and($moving->meta)->toBe(['publish_note' => 'Ship it']);
});

it('flags a column over the WIP limit', function () {
    ExampleSettings::save(['wipLimit' => 1]);
    Example::factory()->count(2)->review()->create();

    Livewire::actingAs($this->user)
        ->test('example::pages.kanban')
        ->assertSee(__('example::pages.over_limit', ['limit' => 1]));
});

it('ignores unknown columns and refuses without permission', function () {
    $example = Example::factory()->draft()->create();

    Livewire::actingAs($this->user)->test('example::pages.kanban')->call('moveItem', $example->id, 0, 'archived');
    expect($example->refresh()->status)->toBe(ExampleStatus::Draft);

    $readOnly = User::factory()->create();
    $readOnly->roles()->attach(Role::factory()->has(RolePermission::factory()->forModule('example', 'manage')->readOnly())->create());

    Livewire::actingAs($readOnly)->test('example::pages.kanban')->call('moveItem', $example->id, 0, 'review')->assertDispatched('notify');
    expect($example->refresh()->status)->toBe(ExampleStatus::Draft);
});
