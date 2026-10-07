<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\DeletesAttachments;
use VmEngine\Example\Models\ExampleAttachment;

new class extends Component
{
    use DeletesAttachments;

    #[Locked]
    public int $exampleId;

    public function mount(int $exampleId): void
    {
        $this->exampleId = $exampleId;
    }

    public function placeholder(): string
    {
        return '<div class="grid grid-cols-2 gap-3 sm:grid-cols-4"><div class="h-24 animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div><div class="h-24 animate-pulse rounded-lg bg-gray-100 dark:bg-gray-800"></div></div>';
    }

    /**
     * @return Collection<int, ExampleAttachment>
     */
    #[Computed()]
    public function attachments(): Collection
    {
        return ExampleAttachment::query()->where('example_id', $this->exampleId)->orderBy('position')->get();
    }
};
