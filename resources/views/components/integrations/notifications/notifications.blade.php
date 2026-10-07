<div x-data="{ pageName: 'Notifications', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::integrations.notifications')">
        <div class="syn-panel-body space-y-4 px-5 py-4 text-sm sm:px-6">
            <p>{{ __('example::integrations.notifications_intro') }}</p>
            <div class="flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2">
                    <input
                        class="form-checkbox"
                        type="checkbox"
                        wire:model="withToast"
                    >
                    <span>{{ __('example::integrations.send_test_toast') }}</span>
                </label>
                <button
                    class="btn primary"
                    type="button"
                    wire:click="sendTest"
                ><span class="ph ph-bell-ringing"></span> {{ __('example::integrations.send_test') }}</button>
            </div>
            <pre class="overflow-x-auto rounded bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800">{{ $this->shapeJson() }}</pre>
        </div>
    </x-synapse-panel>
</div>
