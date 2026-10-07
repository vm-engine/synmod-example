<?php

declare(strict_types=1);

namespace VmEngine\Example\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * ApexCharts wrapper (Alpine: exampleChart in resources/js/example.js).
 * Donut/pie take a flat number list; bar/line take [['name' => .., 'data' => [..]]].
 * Livewire hosts push new data with
 * $this->dispatch('example-chart-{id}', series: [...], labels: [...]).
 */
class Chart extends Component
{
    /**
     * @param  list<int|float>|list<array{name: string, data: list<int|float>}>  $series
     * @param  list<string>  $labels
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $series,
        public array $labels = [],
        public int $height = 280,
    ) {}

    /**
     * @return array{type: string, series: array<mixed>, labels: list<string>, height: int}
     */
    public function config(): array
    {
        return ['type' => $this->type, 'series' => $this->series, 'labels' => $this->labels, 'height' => $this->height];
    }

    public function hasData(): bool
    {
        $values = isset($this->series[0]['data'])
            ? array_merge(...array_column($this->series, 'data'))
            : $this->series;

        return array_sum($values) > 0;
    }

    public function render(): View
    {
        return view('example::blade.chart');
    }
}
