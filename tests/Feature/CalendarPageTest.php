<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-10-07 09:00:00');
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('shows examples due in the visible month and navigates', function () {
    Example::factory()->create(['text' => 'October thing', 'due_at' => '2026-10-15']);
    Example::factory()->create(['text' => 'November thing', 'due_at' => '2026-11-02']);

    Livewire::actingAs($this->user)
        ->test('example::pages.calendar')
        ->assertSet('month', '2026-10')
        ->assertSee('October thing')
        ->assertDontSee('November thing')
        ->call('setMonth', '2026-11')
        ->assertSee('November thing')
        ->call('setMonth', 'garbage')
        ->assertSet('month', '2026-11');
});

it('normalizes a bad month from the URL', function () {
    Livewire::withQueryParams(['month' => '2026-13'])->actingAs($this->user)
        ->test('example::pages.calendar')
        ->assertSet('month', '2026-10');
});

it('opens an event and quick-creates on a day', function () {
    $example = Example::factory()->create(['due_at' => '2026-10-15']);

    Livewire::actingAs($this->user)->test('example::pages.calendar')->call('openEvent', (string) $example->id)->assertRedirect();

    Livewire::actingAs($this->user)
        ->test('example::pages.calendar')
        ->call('createOn', '2026-10-20')
        ->assertSet('quickDate', '2026-10-20')
        ->assertDispatched('open-modal-calendar-create')
        ->call('saveQuick')
        ->assertHasErrors(['quickText' => 'required'])
        ->set('quickText', 'Made on the calendar')
        ->set('quickEmail', 'c@example.com')
        ->call('saveQuick')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal-calendar-create')
        ->assertSee('Made on the calendar');

    expect(Example::query()->where('text', 'Made on the calendar')->value('due_at')?->toDateString())->toBe('2026-10-20');
});
