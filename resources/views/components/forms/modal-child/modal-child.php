<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\FormPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use FormPatternPage;
    use WithPagination;

    public function title(): string
    {
        return __('example::forms.modal_child');
    }

    #[On('example-saved')]
    public function refreshList(): void
    {
        unset($this->examples);
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()->orderByDesc('updated_at')->paginate(10);
    }
};
