<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Models\ExampleTag;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

new class extends Component
{
    use GuardsBackendPermission;

    public ?int $editingId = null;

    public string $editName = '';

    public string $editColor = '';

    public string $newName = '';

    public string $newColor = '#465fff';

    public function mount(): void
    {
        synav()->setActiveMenu('example.tags');
    }

    public function title(): string
    {
        return __('example::lists.tags');
    }

    public function startEdit(int $id): void
    {
        $tag = ExampleTag::query()->findOrFail($id);
        $this->editingId = $tag->id;
        $this->editName = $tag->name;
        $this->editColor = $tag->color ?? '#465fff';
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editName', 'editColor');
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        if (! $this->guardAction('example.tag.update') || $this->editingId === null) {
            return;
        }

        $this->validate([
            'editName' => ['required', 'string', 'max:50'],
            'editColor' => ['required', 'hex_color'],
        ]);

        ExampleTag::query()->findOrFail($this->editingId)->update(['name' => $this->editName, 'color' => $this->editColor]);
        $this->cancelEdit();
        $this->saved();
    }

    public function addTag(): void
    {
        if (! $this->guardAction('example.tag.create')) {
            return;
        }

        $this->validate([
            'newName' => ['required', 'string', 'max:50'],
            'newColor' => ['required', 'hex_color'],
        ]);

        ExampleTag::query()->create(['name' => $this->newName, 'color' => $this->newColor]);
        $this->reset('newName');
        $this->saved();
    }

    public function delete(string $token): void
    {
        // Closes x-synapse-confirm-dialog on every path.
        $this->dispatch('synapse-confirmed');

        if (! $this->guardAction('example.tag.delete')) {
            return;
        }

        $id = ExampleTag::validateDeleteToken($token);
        $tag = $id ? ExampleTag::query()->find($id) : null;

        if ($tag === null) {
            $this->dispatch('notify', variant: 'danger', title: 'Error', message: __('example::labels.invalid_delete_token'));

            return;
        }

        $tag->delete();
        unset($this->tags);
        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.tag_deleted'));
    }

    #[Computed()]
    public function tags()
    {
        return ExampleTag::query()->withCount('examples')->orderBy('name')->get();
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(label: __('example::lists.tags'), icon: 'ph ph-tag');
    }

    /**
     * @return list<string>
     */
    #[Computed()]
    public function swatches(): array
    {
        return ['#ef4444', '#f59e0b', '#22c55e', '#14b8a6', '#3b82f6', '#6366f1', '#a855f7', '#ec4899'];
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }

    private function saved(): void
    {
        unset($this->tags);
        $this->dispatch('notify', variant: 'success', title: 'Success', message: __('example::lists.tag_saved'));
    }
};
