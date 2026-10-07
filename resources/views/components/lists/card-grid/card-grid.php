<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use ListPatternPage;
    use WithPagination;

    #[Url()]
    public string $status = 'all';

    public function title(): string
    {
        return __('example::lists.card_grid');
    }

    public function setStatus(string $status): void
    {
        $this->status = ExampleStatus::tryFrom($status) !== null ? $status : 'all';
        $this->resetPage();
    }

    /**
     * @return array<string, int> status value => count
     */
    #[Computed()]
    public function statusCounts(): array
    {
        $counts = Example::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $result = [];
        foreach (ExampleStatus::cases() as $case) {
            $result[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $result;
    }

    #[Computed()]
    public function examples()
    {
        return Example::query()
            ->with(['category', 'tags'])
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('id')
            ->paginate(12);
    }
};
