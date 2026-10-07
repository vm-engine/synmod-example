<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use VmEngine\Example\Imports\ExampleImportProcessor;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Notifications\ExampleImportFinished;
use VmEngine\Example\Notifications\ExamplePublished;
use VmEngine\Synapse\Enums\ExcelTaskStatus;
use VmEngine\Synapse\Jobs\ProcessExcelImport;
use VmEngine\Synapse\Models\ExcelImport;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['synapps.apps.notifications.enabled' => true]);
    Notification::fake();
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach(Role::factory()->admin()->create());
    ExampleCategory::factory()->create(['name' => 'News']);
});

function runExampleImport(User $user, array $rows, int $chunkSize = 2): ExcelImport
{
    $path = tempnam(sys_get_temp_dir(), 'imp').'.csv';
    $handle = fopen($path, 'w');
    fputcsv($handle, ['title', 'email', 'status', 'category', 'due', 'note']);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);

    $task = ExcelImport::create([
        'status' => ExcelTaskStatus::Pending,
        'file_path' => $path,
        'user_id' => $user->id,
        'reference' => 'example-import-'.$user->id,
        'processor_class' => ExampleImportProcessor::class,
        'options' => ['mapping' => ExampleImportProcessor::COLUMNS, 'starting_row' => 2, 'chunk_size' => $chunkSize],
    ]);

    (new ProcessExcelImport($task->id))->handle();

    return $task->refresh();
}

it('imports valid rows, skips invalid ones with row numbers, and reports both', function () {
    $task = runExampleImport($this->user, [
        ['Good one', 'a@example.com', 'draft', 'news', '2026-11-01', ''],
        ['', 'b@example.com', 'draft', '', '', ''],
        ['Bad email', 'nope', 'review', '', '', ''],
        ['Published', 'c@example.com', 'published', 'News', '', 'Imported'],
        ['Unknown category', 'd@example.com', 'draft', 'Nope', '', ''],
    ]);

    expect($task->status)->toBe(ExcelTaskStatus::Completed)
        ->and($task->options['report']['imported'])->toBe(2)
        ->and($task->options['report']['skipped'])->toBe(3)
        ->and(array_column($task->options['report']['errors'], 'row'))->toBe([3, 4, 6])
        ->and(Example::query()->where('text', 'Good one')->value('category_id'))->not->toBeNull()
        ->and(Example::query()->where('text', 'Published')->first()?->meta)->toBe(['publish_note' => 'Imported'])
        ->and(Example::query()->where('text', 'Good one')->value('created_by'))->toBe($this->user->id)
        ->and(auth()->check())->toBeFalse();

    Notification::assertNotSentTo($this->admin, ExamplePublished::class);
    Notification::assertSentTo($this->user, ExampleImportFinished::class, fn (ExampleImportFinished $n): bool => $n->imported === 2 && $n->skipped === 3);
});

it('caps the reported errors at 50', function () {
    $rows = array_fill(0, 60, ['', 'x@example.com', 'draft', '', '', '']);

    $task = runExampleImport($this->user, $rows, chunkSize: 25);

    expect($task->options['report']['skipped'])->toBe(60)
        ->and($task->options['report']['errors'])->toHaveCount(50);
});
