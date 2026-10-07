<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a wire-ignored chart host listening for its update event', function () {
    $html = Blade::render('<x-example::chart id="status" type="donut" :series="[3, 1]" :labels="[\'Draft\', \'Published\']" />');

    expect($html)->toContain('wire:ignore')
        ->toContain('exampleChart(')
        ->toContain('@example-chart-status.window="update($event.detail)"')
        ->toContain('&quot;type&quot;:&quot;donut&quot;')
        ->toContain('&quot;series&quot;:[3,1]');
});

it('renders an empty state instead of a chart when there is no data', function () {
    $html = Blade::render('<x-example::chart id="empty" type="donut" :series="[0, 0]" :labels="[\'a\', \'b\']" />');

    expect($html)->not->toContain('exampleChart(')
        ->toContain(__('example::pages.nothing_here'));
});
