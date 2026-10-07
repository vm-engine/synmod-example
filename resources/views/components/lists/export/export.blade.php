<div x-data="{ pageName: 'Export', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.export')">
        <div class="syn-panel-body space-y-4 px-5 py-4 sm:px-6">
            @include('example::partials.example-filter-row', ['statuses' => $this->statuses, 'categories' => $this->categories])

            <p class="text-sm text-gray-500">{{ __('example::lists.export_hint') }}</p>
            <p class="text-sm font-medium">{{ __('example::lists.showing', ['shown' => $this->previewCount, 'total' => $this->previewCount]) }}</p>

            <div class="flex flex-wrap items-start gap-3">
                <x-synapse-excel-export-button
                    mode="sync"
                    action="exportSync"
                    :label="__('example::lists.export_sync')"
                />
                <x-synapse-excel-export-button
                    mode="async"
                    :task="$this->exportTask"
                    action="exportToExcel"
                    download-action="downloadExport"
                    dismiss-action="dismissExport"
                    :label="__('example::lists.export_async')"
                />
            </div>
        </div>
    </x-synapse-panel>
</div>
