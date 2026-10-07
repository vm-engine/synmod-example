<div x-data="{ pageName: 'Empty states', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <x-synapse-panel :title="__('example::pages.first_run')">
            @include('example::partials.empty-state', [
                'icon' => 'ph ph-sparkle',
                'title' => __('example::pages.first_run'),
                'text' => __('example::pages.first_run_text'),
                'action' => ['label' => __('example::pages.create_example'), 'href' => backend_route('example.editor')],
            ])
        </x-synapse-panel>
        <x-synapse-panel :title="__('example::pages.no_results')">
            @include('example::partials.empty-state', [
                'icon' => 'ph ph-magnifying-glass',
                'title' => __('example::pages.no_results'),
                'text' => __('example::pages.no_results_text'),
                'action' => ['label' => __('example::pages.clear_filters'), 'click' => 'clearFilters'],
            ])
        </x-synapse-panel>
        <x-synapse-panel :title="__('example::pages.error_state')">
            @include('example::partials.empty-state', [
                'icon' => 'ph ph-warning-circle',
                'title' => __('example::pages.error_state'),
                'text' => __('example::pages.error_state_text'),
                'action' => ['label' => __('example::pages.retry'), 'click' => '$refresh'],
            ])
        </x-synapse-panel>
    </div>
</div>
