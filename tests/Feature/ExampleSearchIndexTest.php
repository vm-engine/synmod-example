<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleSearchIndex;
use VmEngine\Synapse\Models\Searchable;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate', ['--path' => base_path('vendor/vm-engine/synapse/database/migrations/searchable'), '--realpath' => true])->assertExitCode(0);
});

function indexed(): array
{
    return Searchable::query()->where('source', ExampleSearchIndex::SOURCE)->orderBy('key')->pluck('title', 'key')->all();
}

it('keeps examples in the global search index through their lifecycle', function () {
    $example = Example::factory()->create(['text' => 'Findable thing', 'slug' => 'findable-thing']);
    expect(indexed())->toBe([(string) $example->id => 'Findable thing']);

    $example->update(['text' => 'Renamed thing']);
    expect(indexed())->toBe([(string) $example->id => 'Renamed thing']);

    $row = Searchable::query()->where('source', ExampleSearchIndex::SOURCE)->sole();
    expect($row->acl)->toBe('example.manage')
        ->and($row->route)->toBe('example.show')
        ->and($row->route_params)->toBe(['id' => $example->id])
        ->and($row->keywords)->toContain('findable-thing');

    $example->delete();
    expect(indexed())->toBe([]);

    $example->restore();
    expect(indexed())->toHaveCount(1);

    $example->forceDelete();
    expect(indexed())->toBe([]);
});

it('drops bulk-deleted examples and reindexes from scratch', function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $user = User::factory()->create();
    $user->roles()->attach($role);
    $examples = Example::factory()->count(3)->create();

    Livewire::actingAs($user)->test('example::lists.bulk-actions')
        ->set('selected', [$examples[0]->id])
        ->call('bulkDelete');
    expect(indexed())->toHaveCount(2);

    Searchable::query()->delete();
    $this->artisan('example:search-reindex')->expectsOutputToContain('2 examples indexed')->assertExitCode(0);
    expect(indexed())->toHaveCount(2);
});
