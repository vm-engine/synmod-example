<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FiltersExamples;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleSettings;
use VmEngine\Synapse\Traits\RemembersQueryParams;

new class extends Component
{
    use FiltersExamples;
    use ListPatternPage;
    use RemembersQueryParams;
    use WithPagination;

    public int $perPage = 15;

    public function mount(): void
    {
        $this->perPage = ExampleSettings::perPage();
    }

    #[Computed()]
    public function showDueColumn(): bool
    {
        return ExampleSettings::showDueColumn();
    }

    /**
     * @return array<int, string>
     */
    protected function rememberedParams(): array
    {
        return ['q', 'filterStatus', 'filterCategory', 'dueFrom', 'dueTo'];
    }

    public function title(): string
    {
        return __('example::lists.inline_filters');
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    #[Computed()]
    public function examples()
    {
        return ($this->exportQuery())()->paginate($this->perPage);
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
