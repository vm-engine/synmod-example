<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VmEngine\Example\Imports\ExampleImportProcessor;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\Synapse\Enums\ExcelTaskStatus;
use VmEngine\Synapse\Models\ExcelImport;
use VmEngine\Synapse\Services\Excel\ExcelImporter;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;
    use IntegrationPatternPage;
    use WithFileUploads;

    public ?TemporaryUploadedFile $upload = null;

    #[Locked]
    public ?int $taskId = null;

    public function mount(?int $task = null): void
    {
        if ($task === null) {
            return;
        }

        abort_unless(ExcelImport::query()->whereKey($task)->where('user_id', auth()->id())->exists(), 404);
        $this->taskId = $task;
    }

    public function title(): string
    {
        return __('example::integrations.import');
    }

    public function startImport(): void
    {
        if (! $this->guardAction('example.manage.create')) {
            return;
        }

        $this->validate(['upload' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);

        $extension = strtolower($this->upload->getClientOriginalExtension()) === 'csv' ? 'csv' : 'xlsx';
        $path = $this->upload->storeAs('imports', 'example-'.auth()->id().'-'.now()->format('YmdHis').'.'.$extension, 'local');

        $task = ExcelImporter::make(Storage::disk('local')->path((string) $path))
            ->startingRow(2)
            ->mapColumns(ExampleImportProcessor::COLUMNS)
            ->forUser((int) auth()->id())
            ->reference('example-import-'.auth()->id())
            ->dispatch(ExampleImportProcessor::class, chunkSize: 200);

        $this->reset('upload');
        $this->taskId = $task->id;
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'example-tpl').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Title', 'Email', 'Status', 'Category', 'Due date', 'Publish note']));
        $writer->addRow(Row::fromValues(['My first import', 'someone@example.com', 'draft', '', now()->addWeek()->toDateString(), '']));
        $writer->close();

        return response()->download($path, 'example-import-template.xlsx')->deleteFileAfterSend();
    }

    #[Computed()]
    public function task(): ?ExcelImport
    {
        return $this->taskId === null ? null : ExcelImport::query()->whereKey($this->taskId)->where('user_id', auth()->id())->first();
    }

    #[Computed()]
    public function running(): bool
    {
        return in_array($this->task?->status, [ExcelTaskStatus::Pending, ExcelTaskStatus::Processing], true);
    }

    #[Computed()]
    public function isPending(): bool
    {
        return $this->task?->status === ExcelTaskStatus::Pending;
    }

    #[Computed()]
    public function isCompleted(): bool
    {
        return $this->task?->status === ExcelTaskStatus::Completed;
    }

    /**
     * @return array{imported: int, skipped: int, errors: list<array{row: int, messages: list<string>}>}
     */
    #[Computed()]
    public function report(): array
    {
        return $this->task->options['report'] ?? ['imported' => 0, 'skipped' => 0, 'errors' => []];
    }
};
