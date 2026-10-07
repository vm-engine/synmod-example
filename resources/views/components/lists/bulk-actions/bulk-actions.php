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

new class extends Component
{
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

        if ($target === null || ! $this->allowed('example.manage.update')) {
            return;
        }

        $count = $this->bulkQuery()->update(['status' => $target->value, 'updated_by' => auth()->id()]);
        $this->finishBulk(__('example::lists.bulk_status_done', ['count' => $count, 'status' => $target->label()]));
    }

    public function bulkDelete(): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        if (! $this->allowed('example.manage.delete')) {
            return;
        }

        $count = $this->bulkQuery()->delete();
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

    private function allowed(string $acl): bool
    {
        if (auth()->user()?->can($acl)) {
            return true;
        }

        $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.not_allowed'));

        return false;
    }

    private function finishBulk(string $message): void
    {
        $this->clearSelection();
        unset($this->examples, $this->selectedCount);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: $message);
    }
};
