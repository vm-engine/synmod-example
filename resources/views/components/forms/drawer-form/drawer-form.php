<?php

declare(strict_types=1);

use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleSettings;

new class extends Component
{
    use FormPatternPage;
    use WithPagination;

    public ?int $editingId = null;

    public string $text = '';

    public string $email = '';

    public ?int $category_id = null;

    public string $status = 'draft';

    public function title(): string
    {
        return __('example::forms.drawer');
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'text', 'email', 'category_id', 'status');
        $this->status = ExampleSettings::defaultStatus();
        $this->dispatch('example-drawer-open');
    }

    public function edit(int $id): void
    {
        $example = Example::query()->findOrFail($id);
        $this->resetValidation();
        $this->editingId = $example->id;
        $this->text = $example->text;
        $this->email = $example->email;
        $this->category_id = $example->category_id;
        $this->status = $example->status->value;
        $this->dispatch('example-drawer-open');
    }

    public function save(): void
    {
        $action = $this->editingId ? 'update' : 'create';

        if (! auth()->user()?->can('example.manage.'.$action)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $data = $this->validate([
            'text' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            'status' => ['required', Rule::enum(ExampleStatus::class)],
        ]);

        $this->editingId
            ? Example::query()->findOrFail($this->editingId)->update($data)
            : Example::query()->create($data);

        unset($this->examples);
        $this->dispatch('example-drawer-close');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.saved'));
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()->with('category')->orderByDesc('updated_at')->paginate(10);
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    #[Computed()]
    public function categories(): array
    {
        return ExampleCategory::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (ExampleCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
    }

    #[Computed()]
    public function drawerTitle(): string
    {
        return e($this->editingId ? __('example::labels.edit') : __('example::forms.new_example'));
    }
};
