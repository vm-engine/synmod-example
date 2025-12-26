<?php

namespace VmEngine\Example\Livewire;

use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

class ExampleList extends Component
{
    use WithPagination;

    #[Url()]
    public $q;

    #[Url()]
    public $filterOption = null;

    #[Url()]
    public $filterCategories = [];

    public $limit = 10;

    public $sort = 'id';

    public $sortDirection = 'asc';

    public function updated($property)
    {
        $this->resetPage();
    }

    public function updatedFilterCategories($value)
    {
        // Ensure empty arrays are properly handled
        if (empty($value)) {
            $this->filterCategories = [];
        }
        $this->resetPage();
    }

    public function updating()
    {
        if ($this->filterOption === 'all') {
            $this->filterOption = null;
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

    public function filterByCategory($categoryId)
    {
        // Toggle category in filter
        if (in_array($categoryId, $this->filterCategories)) {
            $this->filterCategories = array_values(array_diff($this->filterCategories, [$categoryId]));
        } else {
            $this->filterCategories[] = $categoryId;
        }
        $this->resetPage();
    }

    public function delete($token)
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
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => 'Data not found.',
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

        // Dispatch browser event for immediate notification (no page reload)
        $this->dispatch('notify',
            variant: 'success',
            title: 'Success',
            message: 'Example deleted successfully.'
        );
    }

    #[Computed()]
    public function exampleList()
    {
        // $this->resetPage();
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

    public function render()
    {
        $options = ['all' => '-- All --'] + Example::$options;
        $categories = ExampleCategory::active()->select('id as value', 'name as label')->get()->toArray();

        $breadcrumbs = Breadcrumbs::make(
            label: 'Example List',
            icon: 'fa-solid fa-list',
        );

        synav()->setActiveMenu('example.index');

        return view('example::livewire.example-list', [
            'options' => $options,
            'categories' => $categories,
            'breadcrumbs' => $breadcrumbs,
        ])->title(__('example::labels.example_list'));
    }
}
