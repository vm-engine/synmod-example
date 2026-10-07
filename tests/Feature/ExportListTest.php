<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use VmEngine\Example\Exports\ExampleExportQuery;
use VmEngine\Example\Models\Example;
use VmEngine\Synapse\Jobs\ProcessExcelExport;
use VmEngine\Synapse\Models\ExcelExport;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = RolePermission::factory()->forModule('example', 'manage')->fullCrud();
    $role = Role::factory()->admin()->has($permission)->create();
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
});

it('downloads a synchronous xlsx', function () {
    Example::factory()->count(3)->create();

    Livewire::actingAs($this->user)
        ->test('example::lists.export')
        ->call('exportSync')
        ->assertFileDownloaded();
});

it('queues a background export with the current filters under a per-user reference', function () {
    Queue::fake();

    Livewire::actingAs($this->user)
        ->test('example::lists.export')
        ->set('filterStatus', 'review')
        ->call('exportToExcel');

    $task = ExcelExport::findByReference('example-export-'.$this->user->id);

    expect($task)->not->toBeNull()
        ->and($task->query_class)->toBe(ExampleExportQuery::class)
        ->and($task->options['filters']['status'] ?? null)->toBe('review');

    Queue::assertPushed(ProcessExcelExport::class);
});

it('replaces the previous background export for the same user', function () {
    Queue::fake();

    $component = Livewire::actingAs($this->user)->test('example::lists.export');
    $component->call('exportToExcel');
    $component->call('exportToExcel');

    expect(ExcelExport::query()->where('reference', 'example-export-'.$this->user->id)->count())->toBe(1);
});
