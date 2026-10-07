<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Livewire\Concerns\WizardSteps;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Models\ExampleCategory;

new class extends Component
{
    use FormPatternPage;
    use WizardSteps;

    public string $text = '';

    public string $email = '';

    public ?int $category_id = null;

    public string $status = 'draft';

    public string $due_at = '';

    public function title(): string
    {
        return __('example::forms.modal_wizard');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stepRules(): array
    {
        return [
            1 => [
                'text' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'category_id' => ['nullable', 'integer', 'exists:example_categories,id'],
            ],
            2 => [
                'status' => ['required', Rule::enum(ExampleStatus::class)],
                'due_at' => ['nullable', 'date_format:Y-m-d'],
            ],
            3 => [],
        ];
    }

    public function finish(): void
    {
        if (! auth()->user()?->can('example.manage.create')) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_allowed'));

            return;
        }

        $data = $this->validate($this->allStepRules());

        DB::transaction(fn () => Example::query()->create([...$data, 'due_at' => $data['due_at'] ?: null]));

        $this->reset('text', 'email', 'category_id', 'status', 'due_at', 'step');
        unset($this->recent);
        $this->dispatch('close-modal-example-wizard');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.created'));
    }

    /**
     * @return list<string>
     */
    #[Computed()]
    public function stepLabels(): array
    {
        return [__('example::forms.step_basics'), __('example::forms.step_schedule'), __('example::forms.step_review')];
    }

    #[Computed()]
    public function categoryName(): ?string
    {
        return $this->category_id ? ExampleCategory::query()->whereKey($this->category_id)->value('name') : null;
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
    public function statusLabel(): ?string
    {
        return ExampleStatus::tryFrom($this->status)?->label();
    }

    #[Computed()]
    public function recent()
    {
        return Example::query()->latest('id')->limit(5)->get();
    }
};
