<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use VmEngine\Example\Models\Example;

new class extends Component
{
    #[Locked]
    public string $kind = 'recent';

    public function mount(string $kind = 'recent'): void
    {
        $this->kind = in_array($kind, ['recent', 'due-soon'], true) ? $kind : 'recent';
    }

    public function placeholder(): string
    {
        return '<div class="space-y-3 px-5 py-4 sm:px-6"><div class="h-4 w-2/3 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div><div class="h-4 w-1/2 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div><div class="h-4 w-3/5 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div></div>';
    }

    /**
     * @return Collection<int, Example>
     */
    #[Computed()]
    public function items(): Collection
    {
        return $this->kind === 'due-soon'
            ? Example::query()->whereBetween('due_at', [today(), today()->addDays(14)->endOfDay()])->orderBy('due_at')->limit(8)->get()
            : Example::query()->latest('updated_at')->limit(8)->get();
    }
};
