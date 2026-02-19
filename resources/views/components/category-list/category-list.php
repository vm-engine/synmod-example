<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    use WithPagination;

    #[Url()]
    public $q;

    #[Url()]
    public $filterStatus = null;

    #[Url()]
    public int $limit = 10;

    #[Url()]
    public string $sort = 'id';

    #[Url()]
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        synav()->setActiveMenu('example.category');
    }

    public function title(): string
    {
        return __('example::labels.category_list');
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function updating(): void
    {
        if ($this->filterStatus === 'all') {
            $this->filterStatus = null;
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

    public function toggleActive(int $id): void
    {
        try {
            $category = ExampleCategory::findOrFail($id);
            $category->is_active = ! $category->is_active;
            $category->save();

            $this->dispatch('notify',
                variant: 'success',
                title: 'Success',
                message: __('example::labels.category_status_updated'),
            );
        } catch (ModelNotFoundException) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: __('example::labels.category_not_found'),
            );
        } catch (Exception $e) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: $e->getMessage(),
            );
        }
    }

    public function delete(string $token): void
    {
        try {
            $id = ExampleCategory::validateDeleteToken($token);

            if (! $id) {
                throw new Exception(__('example::labels.invalid_delete_token'));
            }

            $category = ExampleCategory::findOrFail($id);
            $category->delete();
        } catch (ModelNotFoundException) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: __('example::labels.category_not_found'),
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
            message: __('example::labels.category_deleted'),
        );
    }

    #[Computed()]
    public function categoryList()
    {
        $model = ExampleCategory::query();

        if (! empty($this->q) && strlen($this->q) > 2) {
            $model->search($this->q);
        }

        if (! is_null($this->filterStatus) && $this->filterStatus !== 'all') {
            $model->where('is_active', $this->filterStatus === 'active');
        }

        return $model
            ->orderBy($this->sort, $this->sortDirection)
            ->paginate($this->limit)
            ->onEachSide(1);
    }

    #[Computed()]
    public function statusOptions(): array
    {
        return [
            'all' => __('example::labels.all'),
            'active' => __('example::labels.active'),
            'inactive' => __('example::labels.inactive'),
        ];
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('example::menu.be.index'),
            icon: 'fa-solid fa-table',
        )->add(
            label: __('example::labels.category_list'),
            icon: 'fa-solid fa-list',
        );
    }
};
