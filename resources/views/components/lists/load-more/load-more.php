<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use ListPatternPage;

    private const STEP = 12;

    public int $limit = self::STEP;

    public function title(): string
    {
        return __('example::lists.load_more');
    }

    public function loadMore(): void
    {
        if ($this->hasMore) {
            $this->limit += self::STEP;
            unset($this->examples, $this->hasMore);
        }
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()->with('category')->orderByDesc('id')->limit($this->limit)->get();
    }

    #[Computed()]
    public function total(): int
    {
        return Example::query()->count();
    }

    #[Computed()]
    public function hasMore(): bool
    {
        return $this->total > $this->limit;
    }
};
