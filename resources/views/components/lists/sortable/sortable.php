<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

new class extends Component
{
    use ListPatternPage;

    #[Url()]
    public ?int $categoryId = null;

    public function mount(): void
    {
        if ($this->categoryId === null || ! ExampleCategory::query()->whereKey($this->categoryId)->exists()) {
            $this->categoryId = ExampleCategory::query()->active()->orderBy('name')->value('id');
        }
    }

    public function title(): string
    {
        return __('example::lists.sortable');
    }

    /**
     * wire:sort handler: $position is the new zero-based index in this category.
     */
    public function moveItem(int|string $id, int $position): void
    {
        // Route middleware does not re-run on Livewire action requests, so authorize here.
        if (! $this->allowed('example.manage.update')) {
            return;
        }

        $ids = $this->items->pluck('id')->map(fn ($value): int => (int) $value)->all();
        $id = (int) $id;

        if (! in_array($id, $ids, true)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.not_in_category'));

            return;
        }

        $ids = array_values(array_diff($ids, [$id]));
        array_splice($ids, max(0, min($position, count($ids))), 0, [$id]);

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $exampleId) {
                Example::query()->whereKey($exampleId)->update(['position' => $index]);
            }
        });

        unset($this->items);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.order_saved'));
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

    #[Computed()]
    public function items()
    {
        return Example::query()
            ->where('category_id', $this->categoryId)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    private function allowed(string $acl): bool
    {
        if (auth()->user()?->can($acl)) {
            return true;
        }

        $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::lists.not_allowed'));

        return false;
    }
};
