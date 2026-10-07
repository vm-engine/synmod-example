<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VmEngine\Synapse\Enums\ExcelTaskStatus;
use VmEngine\Synapse\Models\ExcelExport;
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

function exampleExportTask(User $user, ExcelTaskStatus $status, array $extra = []): ExcelExport
{
    return ExcelExport::query()->create([
        'status' => $status,
        'user_id' => $user->id,
        'reference' => 'example-report-'.$user->id,
        'total_rows' => 10,
        'processed_rows' => 4,
        ...$extra,
    ]);
}

it('polls while the export runs and stops once it completes', function () {
    $task = exampleExportTask($this->user, ExcelTaskStatus::Processing);

    $component = Livewire::actingAs($this->user)
        ->test('example::pages.progress', ['task' => $task->id])
        ->assertSeeHtml('wire:poll.2s')
        ->assertSee('4 of 10');

    $task->update(['status' => ExcelTaskStatus::Completed, 'processed_rows' => 10]);

    $component->call('$refresh')
        ->assertDontSeeHtml('wire:poll.2s')
        ->assertSee(__('example::pages.progress_done'));
});

it('shows the error of a failed export', function () {
    $task = exampleExportTask($this->user, ExcelTaskStatus::Failed, ['error_message' => 'Disk full']);

    Livewire::actingAs($this->user)
        ->test('example::pages.progress', ['task' => $task->id])
        ->assertSee('Disk full')
        ->assertDontSeeHtml('wire:poll.2s');
});

it('falls back to the latest report export, or an empty state', function () {
    Livewire::actingAs($this->user)->test('example::pages.progress')->assertSee(__('example::pages.no_export'));

    exampleExportTask($this->user, ExcelTaskStatus::Pending);

    Livewire::actingAs($this->user)->test('example::pages.progress')->assertSee(__('example::pages.progress_pending'));
});

it('hides other users exports', function () {
    $task = exampleExportTask(User::factory()->create(), ExcelTaskStatus::Processing);

    Livewire::actingAs($this->user)
        ->test('example::pages.progress', ['task' => $task->id])
        ->assertStatus(404);
});
