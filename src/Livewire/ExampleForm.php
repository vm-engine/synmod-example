<?php

namespace VmEngine\Example\Livewire;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use VmEngine\Example\Livewire\Forms\ExampleFormObject;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

class ExampleForm extends Component
{
    use WithFileUploads;

    public ExampleFormObject $form;

    public function mount(?int $id = null)
    {
        if ($id) {
            try {
                $example = Example::query()->findOrFail($id);
                $this->form->setExample($example);
            } catch (ModelNotFoundException) {
                session()->flash('danger', 'Data not found.');
                $this->redirectRoute('backend.example.index', navigate: true);
            }
        }
    }

    public function updating($name, $value)
    {
        if ($name == 'form.masked') {
            $this->form->masked = str_replace('.', '', $value);
        }
    }

    public function save()
    {
        if ($this->form->example) {
            $this->form->update();
            session()->flash('success', 'Example updated successfully.');
        } else {
            $this->form->store();
            session()->flash('success', 'Example created successfully.');
        }

        $this->redirectRoute('backend.example.index', navigate: true);
    }

    public function render()
    {
        $breadcrumbs = Breadcrumbs::make(
            'Example List',
            backend_route('example.index'),
            'fa-solid fa-list',
        )->add(
            label: 'Form',
            icon: 'fa-solid fa-pen',
        );

        synav()->setActiveMenu('example.index');

        $categories = ExampleCategory::active()->select('name as label', 'id as value')->get()->toArray();

        // View: synapps/modules/example/resources/views/livewire/example-form.blade.php
        return view('example::livewire.example-form', [
            'breadcrumbs' => $breadcrumbs,
            'options' => range(1, 9),
            'categories' => $categories,
        ]);
    }
}
