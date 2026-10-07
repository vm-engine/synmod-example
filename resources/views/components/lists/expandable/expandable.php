<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use ListPatternPage;
    use WithPagination;

    public ?int $expandedId = null;

    public function title(): string
    {
        return __('example::lists.expandable');
    }

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()->with(['category', 'tags'])->orderByDesc('id')->paginate(10);
    }
};
