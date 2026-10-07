<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Exports\ExampleExportQuery;
use VmEngine\Example\Livewire\Concerns\FiltersExamples;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleStats;
use VmEngine\Synapse\Services\Excel\ExcelExporter;
use VmEngine\Synapse\Traits\WithExcelExport;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use FiltersExamples;
    use GuardsBackendPermission;
    use PagePatternPage;
    use WithExcelExport;

    public function title(): string
    {
        return __('example::pages.report');
    }

    /**
     * Any filter change: drop the cached matrix and push new series to the chart
     * (it lives under wire:ignore, so it is updated in place, not re-rendered).
     */
    public function updated(): void
    {
        unset($this->matrix);
        $this->dispatch('example-chart-report', ...$this->chart());
    }

    public function exportReport(): void
    {
        if (! $this->guardAction('example.manage.read')) {
            return;
        }

        $reference = 'example-report-'.auth()->id();

        if ($previous = $this->excelExportTask($reference)) {
            $this->dismissExcelExportTask($previous);
        }

        $task = ExcelExporter::make(ExampleExportQuery::class)
            ->withFilters($this->filterValues())
            ->columns(ExampleExportQuery::columns())
            ->reference($reference)
            ->forUser((int) auth()->id())
            ->dispatch(storage_path('app/exports/example-report-'.auth()->id().'-'.now()->format('YmdHis').'.xlsx'));

        $this->redirect(backend_route('example.progress', ['task' => $task->id]), navigate: true);
    }

    /**
     * @return array{rows: list<array{category: string, counts: array<string, int>, total: int}>, totals: array<string, int>, total: int}
     */
    #[Computed()]
    public function matrix(): array
    {
        return ExampleStats::matrix(($this->exportQuery())());
    }

    /**
     * @return array{series: list<array{name: string, data: list<int>}>, labels: list<string>}
     */
    public function chart(): array
    {
        $series = [];

        foreach (ExampleStatus::cases() as $status) {
            $series[] = ['name' => $status->label(), 'data' => array_map(fn (array $row): int => $row['counts'][$status->value], $this->matrix['rows'])];
        }

        return ['series' => $series, 'labels' => array_column($this->matrix['rows'], 'category')];
    }

    #[Computed()]
    public function printUrl(): string
    {
        return backend_route('example.report.print', array_filter([
            'filterCategory' => $this->filterCategory,
            'dueFrom' => $this->dueFrom,
            'dueTo' => $this->dueTo,
        ]));
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
};
