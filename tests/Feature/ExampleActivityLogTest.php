<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;
use VmEngine\SynAuth\Models\UserActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

function exampleActions(): array
{
    return UserActivity::query()->where('module', 'example')->orderBy('id')->pluck('action')->all();
}

it('logs the example lifecycle with whitelisted before/after snapshots', function () {
    $this->actingAs($this->user);

    $example = Example::factory()->draft()->create(['text' => 'Logged', 'content' => '<p>secret body</p>']);
    $example->update(['status' => 'review', 'content' => '<p>changed body</p>']);
    $example->delete();
    $example->restore();
    $example->forceDelete();

    expect(exampleActions())->toBe(['example.created', 'example.updated', 'example.deleted', 'example.restored', 'example.force_deleted']);

    $update = UserActivity::query()->where('action', 'example.updated')->sole();

    expect($update->metadata)->toBe(['before' => ['status' => 'draft'], 'after' => ['status' => 'review']])
        ->and($update->user_id)->toBe($this->user->id)
        ->and($update->description)->toContain('Logged');
});

it('skips updates that only touch unlogged fields, and anonymous writes', function () {
    Example::factory()->create();
    expect(exampleActions())->toBe([]);

    $this->actingAs($this->user);
    $example = Example::factory()->create();
    $example->update(['content' => '<p>only content</p>']);

    expect(exampleActions())->toBe(['example.created']);
});

it('logs bulk writes as one summary entry', function () {
    $examples = Example::factory()->count(3)->draft()->create();
    $this->actingAs($this->user);

    Livewire::test('example::lists.bulk-actions')
        ->set('selected', $examples->pluck('id')->all())
        ->call('bulkSetStatus', 'published')
        ->set('selected', $examples->pluck('id')->all())
        ->call('bulkDelete');

    expect(exampleActions())->toBe(['example.bulk_status', 'example.bulk_deleted']);
});
