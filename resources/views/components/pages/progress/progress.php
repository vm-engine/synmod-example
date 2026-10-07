<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Synapse\Enums\ExcelTaskStatus;
use VmEngine\Synapse\Models\ExcelExport;
use VmEngine\Synapse\Traits\WithExcelExport;

new class extends Component
{
    use PagePatternPage;
    use WithExcelExport;

    #[Locked]
    public ?int $taskId = null;

    public function mount(?int $task = null): void
    {
        if ($task === null) {
            $this->taskId = $this->excelExportTask('example-report-'.auth()->id())?->id;

            return;
        }

        abort_unless(ExcelExport::query()->whereKey($task)->where('user_id', auth()->id())->exists(), 404);
        $this->taskId = $task;
    }

    public function title(): string
    {
        return __('example::pages.progress');
    }

    /**
     * Always re-read with the owner check (taskId is locked, this is belt and braces).
     */
    #[Computed()]
    public function task(): ?ExcelExport
    {
        return $this->taskId === null
            ? null
            : ExcelExport::query()->whereKey($this->taskId)->where('user_id', auth()->id())->first();
    }

    #[Computed()]
    public function running(): bool
    {
        return in_array($this->task?->status, [ExcelTaskStatus::Pending, ExcelTaskStatus::Processing], true);
    }

    #[Computed()]
    public function percent(): int
    {
        $task = $this->task;

        if ($task === null || ! $task->total_rows) {
            return $task?->status === ExcelTaskStatus::Completed ? 100 : 0;
        }

        return (int) min(100, round($task->processed_rows / $task->total_rows * 100));
    }

    #[Computed()]
    public function isCompleted(): bool
    {
        return $this->task?->status === ExcelTaskStatus::Completed;
    }

    #[Computed()]
    public function isPending(): bool
    {
        return $this->task?->status === ExcelTaskStatus::Pending;
    }

    public function download(): ?BinaryFileResponse
    {
        $task = $this->task;

        return $task ? $this->downloadExcelExportFile($task, 'example-report.xlsx') : null;
    }
};
