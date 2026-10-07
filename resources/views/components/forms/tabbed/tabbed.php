<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Livewire\Concerns\HasMetaRepeater;
use VmEngine\Example\Livewire\Forms\ExampleEditorForm;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use FormPatternPage;
    use HasMetaRepeater;

    /** Which tab owns each form field (error badges). */
    private const FIELD_TABS = [
        'text' => 'general', 'slug' => 'general', 'email' => 'general', 'status' => 'general', 'publish_note' => 'general',
        'content' => 'content', 'color' => 'content',
        'meta' => 'meta', 'metaJson' => 'meta', 'schedule' => 'meta', 'due_at' => 'meta',
    ];

    public ExampleEditorForm $form;

    public string $activeTab = 'general';

    public function mount(?int $id = null): void
    {
        if ($id !== null) {
            $this->form->setExample(Example::query()->findOrFail($id));
        }
    }

    public function title(): string
    {
        return __('example::forms.tabbed');
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, ['general', 'content', 'meta'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function updatedFormText(string $value): void
    {
        if (! $this->form->slugTouched) {
            $this->form->slug = Str::slug($value);
        }
    }

    public function updatedFormSlug(): void
    {
        $this->form->slugTouched = true;
    }

    public function save(): void
    {
        $action = $this->form->example ? 'update' : 'create';

        if (! auth()->user()?->can('example.manage.'.$action)) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $created = $this->form->example === null;
        $example = $this->form->save();

        if ($created) {
            session()->flash('success', __('example::forms.created'));
            $this->redirect(backend_route('example.forms.tabbed', ['id' => $example->id]), navigate: true);

            return;
        }

        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.saved'));
    }

    /**
     * Error count per tab, from the current error bag (form.* keys).
     *
     * @return array<string, int>
     */
    #[Computed()]
    public function tabErrors(): array
    {
        $counts = ['general' => 0, 'content' => 0, 'meta' => 0];

        foreach ($this->getErrorBag()->keys() as $key) {
            $field = Str::of($key)->after('form.')->before('.')->toString();
            $tab = self::FIELD_TABS[$field] ?? null;

            if ($tab !== null) {
                $counts[$tab]++;
            }
        }

        return $counts;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed()]
    public function statuses(): array
    {
        return ExampleStatus::options();
    }
};
