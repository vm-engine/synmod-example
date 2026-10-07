<div x-data="{ pageName: 'Example activity', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])
    <livewire:synauth-otp-verify-action />
    <livewire:synauth-activity-metadata-viewer />

    <x-synapse-panel :title="__('example::integrations.activity')">
        <x-slot:toolbar>
            <select
                class="form-input"
                aria-label="{{ __('example::integrations.action') }}"
                wire:model.live="filterAction"
            >
                <option value="">{{ __('example::integrations.all_actions') }}</option>
                @foreach ($this->actionLabels as $action => $label)
                    <option value="{{ $action }}">{{ $label }}</option>
                @endforeach
            </select>
        </x-slot:toolbar>

        <div class="syn-panel-body">
            @if ($this->activities->isEmpty())
                @include('example::partials.empty-state', ['icon' => 'ph ph-clock-counter-clockwise', 'title' => __('example::integrations.no_activity'), 'text' => __('example::integrations.no_activity_text'), 'action' => null])
            @else
                <div class="max-w-full overflow-x-auto">
                    <table class="datatable min-w-full">
                        <thead>
                            <tr>
                                <th><p>{{ __('example::integrations.when') }}</p></th>
                                <th><p>{{ __('example::integrations.who') }}</p></th>
                                <th><p>{{ __('example::integrations.action') }}</p></th>
                                <th><p>{{ __('example::integrations.description') }}</p></th>
                                <th class="datatable-col-actions"><p>{{ __('example::integrations.changes') }}</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->activities as $activity)
                                <tr wire:key="activity-{{ $activity->id }}">
                                    <td><p class="whitespace-nowrap">{{ $activity->created_at?->diffForHumans() }}</p></td>
                                    <td><p>{{ $activity->user?->name ?? '—' }}</p></td>
                                    <td><x-synapse-badge size="sm">{{ $this->actionLabels[$activity->action] ?? $activity->action }}</x-synapse-badge></td>
                                    <td><p>{{ $activity->description }}</p></td>
                                    <td>
                                        @if ($activity->metadata !== null)
                                            <button
                                                class="btn-icon has-tooltip group"
                                                type="button"
                                                wire:click="$dispatch('view-activity-metadata', { activityId: {{ $activity->id }} })"
                                            >
                                                <span class="ph ph-eye"></span>
                                                <span class="tooltip">{{ __('example::integrations.view_changes') }}</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3">{{ $this->activities->links() }}</div>
            @endif
        </div>
    </x-synapse-panel>
</div>
