<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
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
    public $filterStatus = null;

    public function mount(): void
    {
        synav()->setActiveMenu('example.category');
    }

    public function title(): string
    {
        return __('example::labels.category_list');
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortFields(): array
    {
        return ['id', 'name', 'is_active'];
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

        unset($this->categoryList);
        $lastPage = $this->categoryList->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage($lastPage);
            unset($this->categoryList);
        }
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
            ->orderBy($this->validatedSortField(), $this->validatedSortDirection())
            ->paginate($this->perPage)
            ->onEachSide(1);
    }

    #[Computed()]
    public function statusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => __('example::labels.all')],
            ['value' => 'active', 'label' => __('example::labels.active')],
            ['value' => 'inactive', 'label' => __('example::labels.inactive')],
        ];
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
            label: __('example::menu.be.index'),
            icon: 'ph ph-table',
        )->add(
            label: __('example::labels.category_list'),
            icon: 'ph ph-folder',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
