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
    public int $exampleId;

    public function mount(int $exampleId): void
    {
        $this->exampleId = $exampleId;
    }

    public function placeholder(): string
    {
        return '<div class="space-y-2"><div class="h-4 w-1/2 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div><div class="h-4 w-2/5 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div></div>';
    }

    /**
     * @return Collection<int, Example>
     */
    #[Computed()]
    public function related(): Collection
    {
        $categoryId = Example::query()->whereKey($this->exampleId)->value('category_id');

        return $categoryId === null
            ? new Collection
            : Example::query()->where('category_id', $categoryId)->whereKeyNot($this->exampleId)->latest('id')->limit(6)->get();
    }
};
