@if ($hasData())
    <div
        wire:ignore
        x-data="exampleChart({{ json_encode($config()) }})"
        @example-chart-{{ $id }}.window="update($event.detail)"
    >
        <div x-ref="host"></div>
        <p
            class="py-10 text-center text-sm text-gray-500"
            x-cloak
            x-show="failed"
        >{{ __('example::pages.chart_unavailable') }}</p>
    </div>
@else
    <div class="flex flex-col items-center justify-center py-10 text-gray-400">
        <i class="ph ph-chart-bar text-3xl"></i>
        <p class="mt-2 text-sm">{{ __('example::pages.nothing_here') }}</p>
    </div>
@endif
