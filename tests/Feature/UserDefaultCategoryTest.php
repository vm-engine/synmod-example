<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\SynAuth\Livewire\Backend\UserForm;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    foreach ([['example', 'manage'], ['auth', 'users']] as [$module, $feature]) {
        RolePermission::factory()->forModule($module, $feature)->fullCrud()->create(['role_id' => $role->id]);
    }
    $this->role = $role;
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    $this->category = ExampleCategory::factory()->create(['name' => 'Preferred']);
});

it('adds the field to the user form and saves it', function () {
    $target = User::factory()->create();
    $target->roles()->attach($this->role);

    Livewire::actingAs($this->user)
        ->test(UserForm::class, ['userId' => $target->id])
        ->assertSee(__('example::integrations.default_category'))
        ->assertSeeHtml('wire:model="extensionData.example_default_category_id"')
        ->set('extensionData.example_default_category_id', 999999)
        ->call('save')
        ->assertHasErrors(['extensionData.example_default_category_id' => 'exists'])
        ->set('extensionData.example_default_category_id', $this->category->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($target->fresh()->example_default_category_id)->toBe($this->category->id);
});

it('preselects the users category in the editor and calendar quick-create', function () {
    $this->user->forceFill(['example_default_category_id' => $this->category->id])->save();
    Carbon::setTestNow('2026-10-07 09:00:00');

    Livewire::actingAs($this->user)->test('example::example-editor')
        ->assertSet('form.category_id', $this->category->id);

    Livewire::actingAs($this->user)->test('example::pages.calendar')
        ->call('createOn', '2026-10-20')
        ->set('quickText', 'With category')
        ->set('quickEmail', 'c@example.com')
        ->call('saveQuick');

    expect(Example::query()->where('text', 'With category')->value('category_id'))->toBe($this->category->id);
    Carbon::setTestNow();
});

it('ignores a stale category id', function () {
    $this->user->forceFill(['example_default_category_id' => $this->category->id])->save();
    $this->category->delete();

    Livewire::actingAs($this->user)->test('example::example-editor')->assertSet('form.category_id', null);
});
