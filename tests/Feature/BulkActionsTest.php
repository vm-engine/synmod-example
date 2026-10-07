<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

function bulkUser(bool $full = true): User
{
    $permission = RolePermission::factory()->forModule('example', 'manage');
    $permission = $full ? $permission->fullCrud() : $permission->readOnly();
    $role = Role::factory()->admin()->has($permission)->create();
    $user = User::factory()->create();
    $user->roles()->attach($role);

    return $user;
}

it('selects the current page and reports the count', function () {
    Example::factory()->count(20)->create();

    $component = Livewire::actingAs(bulkUser())->test('example::lists.bulk-actions')->call('selectPage');

    expect($component->get('selected'))->toHaveCount(15)
        ->and($component->get('selectedCount'))->toBe(15);
});

it('applies a status to every matching example when select-all is on', function () {
    Example::factory()->count(20)->draft()->create(['text' => 'Batch item']);
    Example::factory()->draft()->create(['text' => 'Other']);

    Livewire::actingAs(bulkUser())
        ->test('example::lists.bulk-actions')
        ->set('q', 'Batch')
        ->call('selectAllMatchingRows')
        ->call('bulkSetStatus', 'published')
        ->assertDispatched('notify')
        ->assertSet('selected', [])
        ->assertSet('selectAllMatching', false);

    expect(Example::query()->where('status', ExampleStatus::Published)->count())->toBe(20)
        ->and(Example::query()->where('text', 'Other')->first()->status)->toBe(ExampleStatus::Draft);
});

it('soft deletes the selected examples', function () {
    $examples = Example::factory()->count(3)->create();

    Livewire::actingAs(bulkUser())
        ->test('example::lists.bulk-actions')
        ->set('selected', [$examples[0]->id, $examples[1]->id])
        ->call('bulkDelete');

    expect(Example::query()->count())->toBe(1)
        ->and(Example::onlyTrashed()->count())->toBe(2);
});

it('clears the selection when the filters change', function () {
    $example = Example::factory()->create();

    Livewire::actingAs(bulkUser())
        ->test('example::lists.bulk-actions')
        ->set('selected', [$example->id])
        ->set('filterStatus', 'review')
        ->assertSet('selected', []);
});

it('refuses bulk actions without the matching permission', function () {
    $example = Example::factory()->draft()->create();

    Livewire::actingAs(bulkUser(full: false))
        ->test('example::lists.bulk-actions')
        ->set('selected', [$example->id])
        ->call('bulkSetStatus', 'published')
        ->call('bulkDelete');

    expect($example->refresh()->status)->toBe(ExampleStatus::Draft)
        ->and($example->trashed())->toBeFalse();
});

it('rejects an unknown status', function () {
    $example = Example::factory()->draft()->create();

    Livewire::actingAs(bulkUser())
        ->test('example::lists.bulk-actions')
        ->set('selected', [$example->id])
        ->call('bulkSetStatus', 'archived');

    expect($example->refresh()->status)->toBe(ExampleStatus::Draft);
});
