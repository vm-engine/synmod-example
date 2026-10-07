<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use VmEngine\Synapse\Enums\ExcelTaskStatus;
use VmEngine\Synapse\Models\ExcelImport;
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

it('queues an uploaded sheet and follows its progress', function () {
    Queue::fake();
    Storage::fake('local');

    $component = Livewire::actingAs($this->user)->test('example::integrations.import')
        ->set('upload', UploadedFile::fake()->createWithContent('examples.csv', "title,email\nA,a@example.com\n"))
        ->call('startImport')
        ->assertHasNoErrors();

    $task = ExcelImport::query()->sole();
    expect($task->user_id)->toBe($this->user->id)
        ->and($task->reference)->toBe('example-import-'.$this->user->id);

    $component->assertSet('taskId', $task->id)->assertSeeHtml('wire:poll.2s');

    $task->update(['status' => ExcelTaskStatus::Completed, 'options' => [...$task->options, 'report' => ['imported' => 1, 'skipped' => 1, 'errors' => [['row' => 3, 'messages' => ['The title field is required.']]]]]]);

    $component->call('$refresh')
        ->assertDontSeeHtml('wire:poll.2s')
        ->assertSee(__('example::integrations.import_done', ['imported' => 1, 'skipped' => 1]))
        ->assertSee('The title field is required.');
});

it('rejects other file types and other users tasks, and serves the template', function () {
    Livewire::actingAs($this->user)->test('example::integrations.import')
        ->set('upload', UploadedFile::fake()->create('evil.php', 1))
        ->call('startImport')
        ->assertHasErrors(['upload']);

    $foreign = ExcelImport::create(['status' => ExcelTaskStatus::Completed, 'file_path' => '/tmp/x.csv', 'user_id' => User::factory()->create()->id]);
    Livewire::actingAs($this->user)->test('example::integrations.import', ['task' => $foreign->id])->assertStatus(404);

    Livewire::actingAs($this->user)->test('example::integrations.import')
        ->call('downloadTemplate')
        ->assertFileDownloaded('example-import-template.xlsx');
});
