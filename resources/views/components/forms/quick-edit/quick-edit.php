<?php

declare(strict_types=1);

use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;

    public ?int $exampleId = null;

    public string $text = '';

    public string $status = 'draft';

    public string $due_at = '';

    #[On('quick-edit-load')]
    public function load(int $id): void
    {
        $this->resetValidation();
        $example = Example::query()->find($id);

        if ($example === null) {
            $this->reset('exampleId', 'text', 'status', 'due_at');
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::forms.not_found'));
            $this->dispatch('close-modal-quick-edit');

            return;
        }

        $this->exampleId = $example->id;
        $this->text = $example->text;
        $this->status = $example->status->value;
        $this->due_at = $example->due_at?->toDateString() ?? '';
    }

    public function save(): void
    {
        if (! $this->guardAction('example.manage.update')) {
            return;
        }

        $this->validate([
            'text' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ExampleStatus::class)],
            'due_at' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $example = Example::query()->find($this->exampleId);

        if ($example === null) {
            $this->load((int) $this->exampleId);

            return;
        }

        $example->update(['text' => $this->text, 'status' => $this->status, 'due_at' => $this->due_at ?: null]);

        $this->dispatch('example-saved', id: $example->id);
        $this->dispatch('close-modal-quick-edit');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::forms.saved'));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function statusOptions(): array
    {
        return ExampleStatus::options();
    }
};
