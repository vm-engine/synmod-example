<div x-data="{ pageName: 'Component gallery', isHome: false, drawerOpen: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])
    <x-synapse-confirm-dialog />
    <x-synapse-lightbox name="gallery" />

    <div class="space-y-5">
        <x-synapse-panel :title="__('example::components.alerts')">
            <div class="syn-panel-body space-y-3 px-5 py-4 sm:px-6">
                <x-synapse-alert
                    type="success"
                    message="Success alert"
                />
                <x-synapse-alert
                    type="info"
                    message="Info alert"
                    :dismissible="true"
                />
                <x-synapse-alert
                    type="warning"
                    title="Warning"
                    message="With a title and a border."
                    :border="true"
                />
                <x-synapse-alert
                    type="danger"
                    message="Solid danger alert"
                    :solid="true"
                />
                <x-synapse-copy-button :text="$this->snippets['alerts']" />
            </div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::components.badges')">
            <div class="syn-panel-body flex flex-wrap items-center gap-2 px-5 py-4 sm:px-6">
                @foreach (['gray', 'brand', 'success', 'warning', 'danger', 'info'] as $color)
                    <x-synapse-badge :color="$color">{{ ucfirst($color) }}</x-synapse-badge>
                @endforeach
                <x-synapse-badge
                    color="success"
                    size="sm"
                    icon="ph ph-check"
                >Small with icon</x-synapse-badge>
                <x-synapse-copy-button :text="$this->snippets['badges']" />
            </div>
        </x-synapse-panel>

        <x-synapse-panel
            :title="__('example::components.panels')"
            collapsible
        >
            <x-slot:toolbar>
                <x-synapse-copy-button :text="$this->snippets['panels']" />
            </x-slot:toolbar>
            <div class="syn-panel-body px-5 py-4 text-sm sm:px-6">{{ __('example::components.panel_body') }}</div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::components.stat_tiles')">
            <div class="syn-panel-body grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-3 sm:px-6">
                <x-synapse-stat-tile
                    icon="ph ph-cube"
                    color="blue"
                    label="Tile"
                    value="1,234"
                />
                <x-synapse-stat-tile
                    variant="card"
                    icon="ph ph-hourglass-medium"
                    color="red"
                    tinted
                    label="Tinted card"
                    value="42"
                    sublabel="> 30 min old"
                />
                <x-synapse-stat-tile
                    variant="card"
                    icon="ph ph-chart-line-up"
                    color="green"
                    icon-position="background"
                    label="Background icon"
                    value="98%"
                />
            </div>
            <div class="px-5 pb-4 sm:px-6"><x-synapse-copy-button :text="$this->snippets['stat_tiles']" /></div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::components.overlays')">
            <div class="syn-panel-body flex flex-wrap gap-2 px-5 py-4 sm:px-6">
                <button
                    class="btn secondary"
                    type="button"
                    data-url="{{ $this->lightboxImage }}"
                    @click="$dispatch('open-lightbox-gallery', { url: $el.dataset.url })"
                ><span class="ph ph-image"></span> {{ __('example::components.open_lightbox') }}</button>
                @foreach ($this->toastVariants as $variant)
                    <button
                        class="btn secondary"
                        type="button"
                        wire:key="toast-{{ $variant }}"
                        wire:click="toast('{{ $variant }}')"
                    >{{ __('example::components.toast', ['variant' => $variant]) }}</button>
                @endforeach
                <button
                    class="btn secondary"
                    type="button"
                    data-title="{{ __('example::components.confirm_title') }}"
                    data-message="{{ __('example::components.confirm_message') }}"
                    data-component="{{ $this->getId() }}"
                    @click="$dispatch('confirm-dialog', {
                        title: $el.dataset.title,
                        message: $el.dataset.message,
                        icon: 'ph ph-question',
                        wireMethod: 'confirmedDemo',
                        wireParams: [],
                        wireComponent: $el.dataset.component
                    })"
                >{{ __('example::components.confirm') }}</button>
                <button
                    class="btn secondary"
                    type="button"
                    @click="drawerOpen = true"
                >{{ __('example::components.open_drawer') }}</button>
            </div>
            <div class="px-5 pb-4 sm:px-6"><x-synapse-copy-button :text="$this->snippets['overlays']" /></div>
        </x-synapse-panel>
    </div>

    <x-synapse-drawer :title="__('example::components.drawer_title')">
        <p class="text-sm">{{ __('example::components.drawer_body') }}</p>
    </x-synapse-drawer>
</div>
