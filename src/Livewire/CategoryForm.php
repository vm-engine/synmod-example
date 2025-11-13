<?php

namespace VmEngine\Example\Livewire;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;
use VmEngine\Example\Livewire\Forms\CategoryFormObject;
use VmEngine\Example\Models\ExampleCategory;

class CategoryForm extends Component
{
    public CategoryFormObject $form;

    protected $listeners = [
        'load-category' => 'loadCategory',
        'reset-category-form' => 'resetForm',
    ];

    public function loadCategory($id)
    {
        try {
            $category = ExampleCategory::query()->findOrFail($id);
            $this->form->setCategory($category);
        } catch (ModelNotFoundException) {
            $this->dispatch('notify', [
                'variant' => 'danger',
                'title' => 'Error',
                'message' => __('example::labels.category_not_found'),
            ]);
            $this->dispatch('close-modal-category-form');
        }
    }

    public function resetForm()
    {
        $this->form->reset();
    }

    public function save()
    {
        if ($this->form->category) {
            $this->form->update();
            $message = __('example::labels.category_updated');
        } else {
            $this->form->store();
            $message = __('example::labels.category_created');
        }

        $this->dispatch('notify', [
            'variant' => 'success',
            'title' => 'Success',
            'message' => $message,
        ]);

        $this->dispatch('close-modal-category-form');
        $this->dispatch('$refresh')->to('category-list');
    }

    public function render()
    {
        return view('example::livewire.category-form');
    }
}
