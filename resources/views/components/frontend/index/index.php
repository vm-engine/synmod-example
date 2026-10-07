<?php

declare(strict_types=1);

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\FrontendPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

new class extends Component
{
    use FrontendPage;
    use WithPagination;

    #[Url()]
    public string $category = '';

    public function pageTitle(): string
    {
        return __('example::frontend.title');
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Example>
     */
    #[Computed()]
    public function examples(): LengthAwarePaginator
    {
        return Example::query()->published()->with('category')
            ->when(ctype_digit($this->category), fn ($query) => $query->where('category_id', (int) $this->category))
            ->latest('id')
            ->paginate(12);
    }

    /**
     * Categories that have published examples.
     *
     * @return Collection<int, ExampleCategory>
     */
    #[Computed()]
    public function categories(): Collection
    {
        return ExampleCategory::query()->whereIn('id', Example::query()->published()->whereNotNull('category_id')->select('category_id'))->orderBy('name')->get(['id', 'name']);
    }
};
