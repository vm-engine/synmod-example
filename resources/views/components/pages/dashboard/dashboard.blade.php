<div x-data="{ pageName: 'Dashboard', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->tiles as $tile)
            <x-synapse-stat-tile
                :icon="$tile['icon']"
                :color="$tile['color']"
                :label="$tile['label']"
                :value="$tile['value']"
            />
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <x-synapse-panel :title="__('example::pages.by_status')">
            <div class="syn-panel-body px-5 py-4 sm:px-6">
                <x-example::chart
                    id="status"
                    type="donut"
                    :series="$this->statusChart['series']"
                    :labels="$this->statusChart['labels']"
                />
            </div>
        </x-synapse-panel>
        <x-synapse-panel :title="__('example::pages.due_per_week')">
            <div class="syn-panel-body px-5 py-4 sm:px-6">
                <x-example::chart
                    id="weeks"
                    type="line"
                    :series="$this->weekChart['series']"
                    :labels="$this->weekChart['labels']"
                />
            </div>
        </x-synapse-panel>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <x-synapse-panel :title="__('example::pages.recent')">
            <livewire:example::pages.dashboard-panel
                kind="recent"
                lazy
            />
        </x-synapse-panel>
        <x-synapse-panel :title="__('example::pages.due_soon')">
            <livewire:example::pages.dashboard-panel
                kind="due-soon"
                lazy
            />
        </x-synapse-panel>
    </div>
</div>
