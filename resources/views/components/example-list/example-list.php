<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\Synapse\Traits\WithSortablePagination;

new class extends Component
{
    use WithPagination;
    use WithSortablePagination;

    #[Url()]
    public $q;

    #[Url()]
    public $filterOption = null;

    #[Url()]
    public $filterCategories = [];

    public function mount(): void
    {
        synav()->setActiveMenu('example.index');
    }

    public function title(): string
    {
        return __('example::labels.example_list');
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortFields(): array
    {
        return ['id', 'text', 'status', 'dropdown', 'datetime', 'due_at'];
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

    public function filterByCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->filterCategories)) {
            $this->filterCategories = array_values(array_diff($this->filterCategories, [$categoryId]));
        } else {
            $this->filterCategories[] = $categoryId;
        }

        $this->resetPage();
    }

    /**
     * Soft delete — the model keeps the uploaded file until a force delete.
     */
    public function delete(string $token): void
    {
        try {
            $id = Example::validateDeleteToken($token);

            if (! $id) {
                throw new Exception(__('example::labels.invalid_delete_token'));
            }

            Example::findOrFail($id)->delete();
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
            unset($this->exampleList);
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
            ->orderBy($this->validatedSortField(), $this->validatedSortDirection())
            ->paginate($this->perPage)
            ->onEachSide(1);
    }

    #[Computed()]
    public function options(): array
    {
        return array_merge(
            [['value' => 'all', 'label' => '-- All --']],
            array_map(fn ($n) => ['value' => $n, 'label' => (string) $n], Example::$options),
        );
    }

    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::active()->select('id as value', 'name as label')->get()->toArray();
    }

    #[Computed()]
    public function drawerTitle(): string
    {
        return '<span class="ph ph-funnel mr-2"></span>'.e(__('example::labels.advanced_filter'));
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('example::labels.example_list'),
            icon: 'ph ph-table',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
