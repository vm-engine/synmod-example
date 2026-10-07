<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Example\Enums\ExampleStatus;
use VmEngine\Example\Livewire\Concerns\PagePatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\Example\Support\ExampleStats;

new class extends Component
{
    use PagePatternPage;

    public function title(): string
    {
        return __('example::pages.dashboard');
    }

    /**
     * @return list<array{icon: string, color: string, label: string, value: string}>
     */
    #[Computed()]
    public function tiles(): array
    {
        $counts = ExampleStats::statusCounts();

        return [
            ['icon' => 'ph ph-cube', 'color' => 'blue', 'label' => __('example::pages.total'), 'value' => number_format(Example::query()->count())],
            ['icon' => 'ph ph-check-circle', 'color' => 'green', 'label' => __('example::pages.published'), 'value' => number_format($counts['published'])],
            ['icon' => 'ph ph-calendar', 'color' => 'indigo', 'label' => __('example::pages.due_this_month'), 'value' => number_format(ExampleStats::dueThisMonthCount())],
            ['icon' => 'ph ph-warning', 'color' => 'red', 'label' => __('example::pages.overdue'), 'value' => number_format(ExampleStats::overdueCount())],
        ];
    }

    /**
     * @return array{series: list<int>, labels: list<string>}
     */
    #[Computed()]
    public function statusChart(): array
    {
        $counts = ExampleStats::statusCounts();

        return [
            'series' => array_values($counts),
            'labels' => array_map(fn (string $status): string => ExampleStatus::from($status)->label(), array_keys($counts)),
        ];
    }

    /**
     * @return array{series: list<array{name: string, data: list<int>}>, labels: list<string>}
     */
    #[Computed()]
    public function weekChart(): array
    {
        $weeks = ExampleStats::duePerWeek();

        return ['series' => [['name' => __('example::pages.due_per_week'), 'data' => $weeks['counts']]], 'labels' => $weeks['labels']];
    }
};
