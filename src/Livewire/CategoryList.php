<?php

namespace VmEngine\Example\Livewire;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

class CategoryList extends Component
{
    use WithPagination;

    #[Url()]
    public $q;

    #[Url()]
    public $filterStatus = null;

    public $limit = 10;

    public $sort = 'id';

    public $sortDirection = 'asc';

    public function updated()
    {
        $this->resetPage();
    }

    public function updating()
    {
        if ($this->filterStatus === 'all') {
            $this->filterStatus = null;
        }
    }

    public function sortData($sort)
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

    public function toggleActive($id)
    {
        try {
            $category = ExampleCategory::findOrFail($id);
            $category->is_active = ! $category->is_active;
            $category->save();

            $this->dispatch('notify', [
                'variant' => 'success',
                'title' => 'Success',
                'message' => __('example::labels.category_status_updated'),
            ]);
        } catch (ModelNotFoundException) {
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => __('example::labels.category_not_found'),
            ]);
        } catch (Exception $e) {
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($token)
    {
        try {
            $id = ExampleCategory::validateDeleteToken($token);
            if (! $id) {
                throw new Exception(__('example::labels.invalid_delete_token'));
            }
            $category = ExampleCategory::findOrFail($id);
            $category->delete();
        } catch (ModelNotFoundException) {
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => __('example::labels.category_not_found'),
            ]);

            return;
        } catch (Exception $e) {
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $this->dispatch('notify',
            variant: 'success',
            title: 'Success',
            message: __('example::labels.category_deleted')
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

    public function render()
    {
        $statusOptions = [
            'all' => __('example::labels.all'),
            'active' => __('example::labels.active'),
            'inactive' => __('example::labels.inactive'),
        ];

        $breadcrumbs = Breadcrumbs::make(
            label: __('example::menu.be.index'),
            icon: 'fa-solid fa-table',
        )->add(
            label: __('example::labels.category_list'),
            icon: 'fa-solid fa-list',
        );

        synav()->setActiveMenu('example.category');

        return view('example::livewire.category-list', [
            'statusOptions' => $statusOptions,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
