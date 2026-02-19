<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\On;
use Livewire\Component;
use VmEngine\Example\Livewire\Forms\CategoryFormObject;
use VmEngine\Example\Models\ExampleCategory;

new class extends Component
{
    public CategoryFormObject $form;

    #[On('load-category')]
    public function loadCategory(int $id): void
    {
        try {
            $category = ExampleCategory::query()->findOrFail($id);
            $this->form->setCategory($category);
        } catch (ModelNotFoundException) {
            $this->dispatch('notify',
                variant: 'danger',
                title: 'Error',
                message: __('example::labels.category_not_found'),
            );
            $this->dispatch('close-modal-category-form');
        }
    }

    #[On('reset-category-form')]
    public function resetForm(): void
    {
        $this->form->reset();
    }

    public function save(): void
    {
        if ($this->form->category) {
            $this->form->update();
            $message = __('example::labels.category_updated');
        } else {
            $this->form->store();
            $message = __('example::labels.category_created');
        }

        $this->dispatch('notify',
            variant: 'success',
            title: 'Success',
            message: $message,
        );

        $this->dispatch('close-modal-category-form');
        $this->dispatch('$refresh')->to('example.category-list');
    }
};
