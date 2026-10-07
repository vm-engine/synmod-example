<div class="mx-auto max-w-4xl space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">{{ __('example::pages.report') }}</h1>
            <p class="text-sm text-gray-500">{{ __('example::pages.generated_at', ['date' => now()->format('Y-m-d H:i')]) }}</p>
            @if ($this->filterSummary !== '')
                <p class="text-sm text-gray-500">{{ $this->filterSummary }}</p>
            @endif
        </div>
        <button
            class="btn primary print:hidden"
            type="button"
            x-data="examplePrint"
            @click="print()"
        ><span class="ph ph-printer"></span> {{ __('example::pages.print') }}</button>
    </div>

    @include('example::partials.status-matrix', ['matrix' => $this->matrix, 'statuses' => $this->statuses])
</div>
