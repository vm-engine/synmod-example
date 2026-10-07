<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\ListPatternPage;
use VmEngine\Example\Models\Example;

new class extends Component
{
    use ListPatternPage;

    public function title(): string
    {
        return __('example::lists.grouped');
    }

    /**
     * One group per status, in enum order, including empty groups.
     *
     * @return list<array{status: ExampleStatus, items: Collection<int, Example>}>
     */
    #[Computed()]
    public function groups(): array
    {
        $byStatus = Example::query()->with('category')->orderBy('text')->get()
            ->groupBy(fn (Example $example): string => $example->status->value);

        return array_map(fn (ExampleStatus $status): array => [
            'status' => $status,
            'items' => $byStatus->get($status->value, new Collection),
        ], ExampleStatus::cases());
    }
};
