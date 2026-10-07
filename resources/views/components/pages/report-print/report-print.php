<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FiltersExamples;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleStats;

new class extends Component
{
    use FiltersExamples;

    /**
     * @return array{rows: list<array{category: string, counts: array<string, int>, total: int}>, totals: array<string, int>, total: int}
     */
    #[Computed()]
    public function matrix(): array
    {
        return ExampleStats::matrix(($this->exportQuery())());
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
     * Human summary of the active filters for the print header.
     */
    #[Computed()]
    public function filterSummary(): string
    {
        $values = $this->filterValues();
        $parts = [];

        if ($values['categoryId'] !== null) {
            $parts[] = __('example::pages.category').': '.(ExampleCategory::query()->whereKey($values['categoryId'])->value('name') ?? '—');
        }
        if ($values['dueFrom'] !== null) {
            $parts[] = __('example::pages.due_from').': '.$values['dueFrom'];
        }
        if ($values['dueTo'] !== null) {
            $parts[] = __('example::pages.due_to').': '.$values['dueTo'];
        }

        return implode(' · ', $parts);
    }

    public function render()
    {
        return $this->view()->layout('example::layouts.print')->title(page_title(__('example::pages.report_print')));
    }
};
