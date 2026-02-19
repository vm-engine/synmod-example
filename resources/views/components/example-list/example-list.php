<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    use WithPagination;

    #[Url()]
    public $q;

    #[Url()]
    public $filterOption = null;

    #[Url()]
    public $filterCategories = [];

    #[Url()]
    public int $limit = 10;

    #[Url()]
    public string $sort = 'id';

    #[Url()]
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        synav()->setActiveMenu('example.index');
    }

    public function title(): string
    {
        return __('example::labels.example_list');
    }

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategories($value): void
    {
        if (empty($value)) {
            $this->filterCategories = [];
        }

        $this->resetPage();
    }

    public function updating(): void
    {
        if ($this->filterOption === 'all') {
            $this->filterOption = null;
        }
    }

    public function sortData(string $sort): void
    {
        if ($this->sort === $sort && $this->sortDirection === 'asc') {
            $this->sortDirection = 'desc';
        } elseif ($this->sort === $sort && $this->sortDirection === 'desc') {
            $this->reset('sort', 'sortDirection');

            return;
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sort = $sort;
    }

    public function filterByCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->filterCategories)) {
            $this->filterCategories = array_values(array_diff($this->filterCategories, [$categoryId]));
        } else {
            $this->filterCategories[] = $categoryId;
        }

        $this->resetPage();
    }

    public function delete(string $token): void
    {
        try {
            $id = Example::validateDeleteToken($token);

            if (! $id) {
                throw new Exception(__('example::labels.invalid_delete_token'));
            }

            $example = Example::findOrFail($id);
            $storage = Storage::disk('public');

            if ($example->file && $storage->exists($example->file)) {
                $storage->delete($example->file);
            }

            $example->delete();
        } catch (ModelNotFoundException) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: 'Data not found.',
            );

            return;
        } catch (Exception $e) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: $e->getMessage(),
            );

            return;
        }

        $this->dispatch('synapse-confirmed');

        $this->dispatch('notify',
            variant: 'success',
            title: 'Success',
            message: 'Example deleted successfully.',
        );

        unset($this->exampleList);
        $lastPage = $this->exampleList->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage($lastPage);
        }
    }

    #[Computed()]
    public function exampleList()
    {
        $model = Example::with('category');

        if (! empty($this->q) && strlen($this->q) > 2) {
            $model->search($this->q);
        }

        if (! is_null($this->filterOption) && $this->filterOption !== 'all') {
            $model->where('dropdown', $this->filterOption);
        }

        if (! empty($this->filterCategories)) {
            $model->whereIn('category_id', $this->filterCategories);
        }

        return $model
            ->orderBy($this->sort, $this->sortDirection)
            ->paginate($this->limit)
            ->onEachSide(1);
    }

    #[Computed()]
    public function options(): array
    {
        return ['all' => '-- All --'] + Example::$options;
    }

    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::active()->select('id as value', 'name as label')->get()->toArray();
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: 'Example List',
            icon: 'fa-solid fa-list',
        );
    }
};
