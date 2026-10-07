<?php

declare(strict_types=1);

namespace VmEngine\Example\Listeners;

use VmEngine\Example\Imports\ExampleImportProcessor;
use VmEngine\Example\Notifications\ExampleImportFinished;
use VmEngine\Synapse\Events\ExcelImportCompleted;
use VmEngine\Synapse\Notifications\Notify;

class NotifyExampleImportFinished
{
    public function handle(ExcelImportCompleted $event): void
    {
        $task = $event->task;

        if ($task->processor_class !== ExampleImportProcessor::class || $task->user_id === null) {
            return;
        }

        $report = $task->options['report'] ?? ['imported' => 0, 'skipped' => 0];

        Notify::make(new ExampleImportFinished($task->id, (int) $report['imported'], (int) $report['skipped']))
            ->toUsers($task->user_id)
            ->withToastr()
            ->send();
    }
}
