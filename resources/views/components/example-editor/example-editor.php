<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Livewire\Concerns\HasAttachments;
use VmEngine\Example\Livewire\Concerns\HasMetaRepeater;
use VmEngine\Example\Livewire\Concerns\HasTagSearch;
use VmEngine\Example\Livewire\Forms\ExampleEditorForm;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;
use VmEngine\Example\Support\ExampleSettings;

new class extends Component
{
    use FormPatternPage;
    use HasAttachments;
    use HasMetaRepeater;
    use HasTagSearch;
    use WithFileUploads;

    public ExampleEditorForm $form;

    public function mount(?int $id = null): void
    {
        if ($id === null) {
            $this->form->status = ExampleSettings::defaultStatus();

            return;
        }

        $example = Example::query()->find($id);

        if ($example === null) {
            session()->flash('danger', __('example::forms.not_found'));
            $this->redirect(backend_route('example.index'), navigate: true);

            return;
        }

        $this->form->setExample($example);
    }

    public function title(): string
    {
        return $this->form->example ? __('example::forms.editor') : __('example::forms.editor_new');
    }

    /**
     * Slug follows the title until the user edits the slug by hand.
     */
    public function updatedFormText(string $value): void
    {
        if (! $this->form->slugTouched) {
            $this->form->slug = Str::slug($value);
        }

        $this->form->validateOnly('text');
    }

    public function updatedFormSlug(): void
    {
        $this->form->slugTouched = true;
        $this->form->validateOnly('slug');
    }

    public function updatedFormEmail(): void
    {
        $this->form->validateOnly('email');
    }

    public function save(): void
    {
        $action = $this->form->example ? 'update' : 'create';

        if (! auth()->user()?->can('example.manage.'.$action)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $this->validate($this->uploadRules($this->form->example));

        $created = $this->form->example === null;
        $example = $this->form->save();
        $this->storeUploads($example);

        if ($created) {
            session()->flash('success', __('example::forms.created'));
            $this->redirect(backend_route('example.editor', ['id' => $example->id]), navigate: true);

            return;
        }

        unset($this->attachmentCount);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.saved'));
    }

    #[Computed()]
    public function attachmentCount(): int
    {
        return $this->form->example?->attachments()->count() ?? 0;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
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
     * @return list<string>
     */
    #[Computed()]
    public function swatches(): array
    {
        return ['#ef4444', '#f59e0b', '#22c55e', '#14b8a6', '#3b82f6', '#465fff', '#a855f7', '#ec4899'];
    }
};
