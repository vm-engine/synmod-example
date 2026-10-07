<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleActivity;
use VmEngine\Example\Support\ExampleNotifier;
use VmEngine\Example\Support\ExampleSearchIndex;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;
    use ListPatternPage;
    use WithPagination;

    #[Url()]
    public string $q = '';

    #[Url()]
    public string $filterStatus = 'all';

    /** @var list<int> */
    public array $selected = [];

    public bool $selectAllMatching = false;

    public function title(): string
    {
        return __('example::lists.bulk_actions');
    }

    public function updatedQ(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function selectPage(): void
    {
        $this->selected = $this->examples->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function selectAllMatchingRows(): void
    {
        $this->selectAllMatching = true;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAllMatching = false;
    }

    /**
     * Checkbox values arrive as strings; keep ints so in_array(..., true) highlighting works.
     */
    public function updatedSelected(): void
    {
        $this->selected = array_values(array_map('intval', $this->selected));
        $this->selectAllMatching = false;
    }

    public function bulkSetStatus(string $status): void
    {
        $target = ExampleStatus::tryFrom($status);

        if ($target === null || ! $this->guardAction('example.manage.update')) {
            return;
        }

        $count = $this->bulkQuery()->update(['status' => $target->value, 'updated_by' => auth()->id()]);
        if ($count > 0) {
            ExampleActivity::summary('example.bulk_status', ['count' => $count, 'status' => $target->label()]);

            if ($target === ExampleStatus::Published) {
                ExampleNotifier::bulkPublished($count);
            }
        }
        $this->finishBulk(__('example::lists.bulk_status_done', ['count' => $count, 'status' => $target->label()]));
    }

    public function bulkDelete(): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        if (! $this->guardAction('example.manage.delete')) {
            return;
        }

        $ids = $this->bulkQuery()->pluck('id')->all();
        $count = $this->bulkQuery()->delete();
        if ($count > 0) {
            ExampleActivity::summary('example.bulk_deleted', ['count' => $count]);
            ExampleSearchIndex::forgetMany($ids);
        }
        $this->finishBulk(__('example::lists.bulk_deleted', ['count' => $count]));
    }

    #[Computed()]
    public function selectedCount(): int
    {
        return $this->selectAllMatching ? $this->filteredQuery()->count() : count($this->selected);
    }

    #[Computed()]
    public function examples()
    {
        return $this->filteredQuery()->with('category')->orderByDesc('id')->paginate(15);
    }

    /**
     * @return Builder<Example>
     */
    private function filteredQuery(): Builder
    {
        return Example::query()
            ->when(mb_strlen(trim($this->q)) > 2, fn (Builder $query) => $query->search(trim($this->q)))
            ->when(ExampleStatus::tryFrom($this->filterStatus) !== null, fn (Builder $query) => $query->where('status', $this->filterStatus));
    }

    /**
     * @return Builder<Example>
     */
    private function bulkQuery(): Builder
    {
        return $this->selectAllMatching
            ? $this->filteredQuery()
            : Example::query()->whereKey(array_map('intval', $this->selected));
    }

    private function finishBulk(string $message): void
    {
        $this->clearSelection();
        unset($this->examples, $this->selectedCount);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: $message);
    }
};
