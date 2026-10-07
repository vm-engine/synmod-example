<div x-data="{ pageName: 'Modal child form', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::forms.modal_child')">
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>{{ __('example::forms.text') }}</p></th>
                            <th><p>{{ __('example::forms.status') }}</p></th>
                            <th><p>{{ __('example::forms.due_at') }}</p></th>
                            <th class="datatable-col-actions"><p>{{ __('example::labels.actions') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->examples as $example)
                            <tr wire:key="quick-{{ $example->id }}">
                                <td><p>{{ $example->text }}</p></td>
                                <td>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </td>
                                <td><p>{{ $example->due_at?->toDateString() ?? '—' }}</p></td>
                                <td>
                                    <div class="text-center">
                                        <button
                                            class="btn-icon warning has-tooltip group"
                                            type="button"
                                            @click="$dispatch('open-modal-quick-edit')"
                                            wire:click="$dispatch('quick-edit-load', { id: {{ $example->id }} })"
                                        >
                                            <span class="ph ph-pencil-simple"></span>
                                            <span class="tooltip">{{ __('example::forms.quick_edit') }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container p-3">{{ $this->examples->links() }}</div>
    </x-synapse-panel>

    <x-synapse-modal
        name="quick-edit"
        maxWidth="lg"
    >
        <livewire:example::forms.quick-edit />
    </x-synapse-modal>
</div>
