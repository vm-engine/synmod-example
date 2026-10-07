<div x-data="{ pageName: 'Export progress', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::pages.progress')">
        <div
            class="syn-panel-body space-y-4 px-5 py-6 sm:px-6"
            @if ($this->running) wire:poll.2s @endif
        >
            @if ($this->task === null)
                @include('example::partials.empty-state', [
                    'icon' => 'ph ph-file-xls',
                    'title' => __('example::pages.no_export'),
                    'text' => __('example::pages.no_export_text'),
                    'action' => ['label' => __('example::pages.back_to_report'), 'href' => backend_route('example.report')],
                ])
            @else
                <div
                    class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                    role="progressbar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $this->percent }}"
                >
                    <div
                        class="bg-brand-500 h-full transition-all"
                        style="width: {{ $this->percent }}%"
                    ></div>
                </div>

                @if ($this->isCompleted)
                    <x-synapse-alert
                        type="success"
                        :message="__('example::pages.progress_done')"
                    />
                    <button
                        class="btn primary"
                        type="button"
                        wire:click="download"
                    ><span class="ph ph-download-simple"></span> {{ __('example::pages.download') }}</button>
                @elseif ($this->running)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->isPending
                            ? __('example::pages.progress_pending')
                            : __('example::pages.progress_running', ['processed' => $this->task->processed_rows, 'total' => $this->task->total_rows ?? '?']) }}
                    </p>
                @else
                    <x-synapse-alert
                        type="danger"
                        :message="__('example::pages.progress_failed', ['error' => $this->task->error_message ?? '—'])"
                    />
                @endif

                <a
                    class="btn secondary"
                    href="{{ backend_route('example.report') }}"
                    wire:navigate
                >{{ __('example::pages.back_to_report') }}</a>
            @endif
        </div>
    </x-synapse-panel>
</div>
