<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Exports\ExampleExportQuery;
use VmEngine\Example\Livewire\Concerns\FiltersExamples;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Models\ExcelExport;
use VmEngine\Synapse\Services\Excel\ExcelExporter;
use VmEngine\Synapse\Traits\WithExcelExport;

new class extends Component
{
    use FiltersExamples;
    use ListPatternPage;
    use WithExcelExport;

    public function title(): string
    {
        return __('example::lists.export');
    }

    /**
     * Synchronous: build the xlsx in this request and stream it back.
     */
    public function exportSync(): BinaryFileResponse
    {
        return ExcelExporter::make(($this->exportQuery())())
            ->columns(ExampleExportQuery::columns())
            ->download('examples-'.now()->format('Ymd-His').'.xlsx');
    }

    /**
     * Queued: one task per user (reference), previous file/task replaced.
     */
    public function exportToExcel(): void
    {
        $reference = $this->exportReference();

        if ($previous = $this->excelExportTask($reference)) {
            $this->dismissExcelExportTask($previous);
        }

        ExcelExporter::make(ExampleExportQuery::class)
            ->withFilters($this->filterValues())
            ->columns(ExampleExportQuery::columns())
            ->reference($reference)
            ->forUser((int) auth()->id())
            ->dispatch(storage_path('app/exports/examples-'.auth()->id().'-'.now()->format('YmdHis').'.xlsx'));

        unset($this->exportTask);
    }

    public function downloadExport(): ?BinaryFileResponse
    {
        $task = $this->exportTask;

        return $task ? $this->downloadExcelExportFile($task, 'examples.xlsx') : null;
    }

    public function dismissExport(): void
    {
        if ($task = $this->exportTask) {
            $this->dismissExcelExportTask($task);
        }

        unset($this->exportTask);
    }

    #[Computed()]
    public function exportTask(): ?ExcelExport
    {
        return $this->excelExportTask($this->exportReference());
    }

    #[Computed()]
    public function previewCount(): int
    {
        return ($this->exportQuery())()->count();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (ExampleCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    private function exportReference(): string
    {
        return 'example-export-'.auth()->id();
    }
};
