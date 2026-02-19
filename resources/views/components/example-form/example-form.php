<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use VmEngine\Example\Livewire\Forms\ExampleFormObject;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\Synapse\Traits\WithReturnUrl;

new class extends Component
{
    use WithFileUploads;
    use WithReturnUrl;

    public ExampleFormObject $form;

    public function mount(?int $id = null): void
    {
        if ($id) {
            try {
                $example = Example::query()->findOrFail($id);
                $this->form->setExample($example);
            } catch (ModelNotFoundException) {
                session()->flash('danger', 'Data not found.');
                $this->redirectBack('backend.example.index');
            }
        }

        synav()->setActiveMenu('example.index');
    }

    public function title(): string
    {
        return $this->form->example
            ? __('example::labels.edit').' '.__('example::labels.example_form')
            : __('example::labels.add').' '.__('example::labels.example_form');
    }

    public function updating($name, $value): void
    {
        if ($name === 'form.masked') {
            $this->form->masked = str_replace('.', '', $value);
        }
    }

    public function save(): void
    {
        if ($this->form->example) {
            $this->form->update();
            session()->flash('success', 'Example updated successfully.');
        } else {
            $this->form->store();
            session()->flash('success', 'Example created successfully.');
        }

        $this->redirectBack('backend.example.index');
    }

    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::active()->select('name as label', 'id as value')->get()->toArray();
    }

    #[Computed()]
    public function options(): array
    {
        return range(1, 9);
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            'Example List',
            backend_route('example.index'),
            'fa-solid fa-list',
        )->add(
            label: 'Form',
            icon: 'fa-solid fa-pen',
        );
    }
};
