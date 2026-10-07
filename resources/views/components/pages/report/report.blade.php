<div x-data="{ pageName: 'Report', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::pages.report')">
        <x-slot:toolbar>
            <div class="flex gap-2">
                <a
                    class="btn secondary"
                    href="{{ $this->printUrl }}"
                    target="_blank"
                    rel="noopener"
                ><span class="ph ph-printer"></span> {{ __('example::pages.print') }}</a>
                <button
                    class="btn primary"
                    type="button"
                    wire:click="exportReport"
                    wire:loading.attr="disabled"
                ><span class="ph ph-file-xls"></span> {{ __('example::pages.export') }}</button>
            </div>
        </x-slot:toolbar>

        <div class="syn-panel-body grid grid-cols-1 gap-3 px-5 py-4 sm:grid-cols-3 sm:px-6">
            <select
                class="form-input"
                aria-label="{{ __('example::pages.category') }}"
                wire:model.live="filterCategory"
            >
                <option value="">{{ __('example::pages.all_categories') }}</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
                @endforeach
            </select>
            <x-synapse-datepicker
                wire:model.live="dueFrom"
                :placeholder="__('example::pages.due_from')"
            />
            <x-synapse-datepicker
                wire:model.live="dueTo"
                :placeholder="__('example::pages.due_to')"
            />
        </div>

        <div class="syn-panel-body px-5 pb-2 sm:px-6">
            <x-example::chart
                id="report"
                type="bar"
                :series="$this->chart()['series']"
                :labels="$this->chart()['labels']"
            />
        </div>

        <div class="syn-panel-body">
            @include('example::partials.status-matrix', ['matrix' => $this->matrix, 'statuses' => $this->statuses])
        </div>
    </x-synapse-panel>
</div>
