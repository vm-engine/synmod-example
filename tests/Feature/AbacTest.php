<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleAuthorRole;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
    $this->author->roles()->attach(ExampleAuthorRole::ensure());
    $this->other = User::factory()->create();
    $this->mine = Example::factory()->create(['text' => 'Mine', 'created_by' => $this->author->id]);
    $this->theirs = Example::factory()->create(['text' => 'Theirs', 'created_by' => $this->other->id]);
});

it('grants update only on the authors own examples', function () {
    expect($this->author->can('example.manage.read'))->toBeTrue()
        ->and($this->author->can('example.manage.create'))->toBeTrue()
        ->and($this->author->can('example.manage.update', $this->mine))->toBeTrue()
        ->and($this->author->can('example.manage.update', $this->theirs))->toBeFalse()
        ->and($this->author->can('example.manage.delete', $this->mine))->toBeTrue()
        ->and($this->author->can('example.manage.update'))->toBeFalse();
});

it('is idempotent', function () {
    $role = ExampleAuthorRole::ensure();

    expect(ExampleAuthorRole::ensure()->id)->toBe($role->id)
        ->and(Role::query()->where('slug', ExampleAuthorRole::SLUG)->count())->toBe(1)
        ->and(RolePermission::query()->where('role_id', $role->id)->count())->toBe(2);
});

it('shows a verdict per row and renames only allowed examples', function () {
    Livewire::actingAs($this->author)
        ->test('example::integrations.abac')
        ->assertSee('Mine')
        ->assertSee(__('example::integrations.allowed'))
        ->assertSee(__('example::integrations.denied'))
        ->call('startRename', $this->mine->id)
        ->set('renameText', 'Mine renamed')
        ->call('rename')
        ->assertDispatched('notify', variant: 'success')
        ->call('startRename', $this->theirs->id)
        ->set('renameText', 'Hijacked')
        ->call('rename')
        ->assertDispatched('notify', variant: 'danger');

    expect($this->mine->fresh()->text)->toBe('Mine renamed')
        ->and($this->theirs->fresh()->text)->toBe('Theirs');
});
