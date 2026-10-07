<div x-data="{ pageName: 'Trash', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <x-synapse-panel :title="__('example::lists.trashed')">
        <x-slot:toolbar>
            <button
                type="button"
                @class(['btn', 'primary' => $view === 'active', 'secondary' => $view !== 'active'])
                wire:click="setView('active')"
            >{{ __('example::lists.active') }}</button>
            <button
                type="button"
                @class(['btn', 'primary' => $view === 'trash', 'secondary' => $view !== 'trash'])
                wire:click="setView('trash')"
            ><span class="ph ph-trash"></span> {{ __('example::lists.trash') }}</button>
        </x-slot:toolbar>

        <div class="syn-panel-body">
            @if ($this->examples->isEmpty())
                <div class="syn-empty-state">
                    <i class="ph ph-trash syn-empty-state-icon"></i>
                    <p class="syn-empty-state-text">{{ __('example::lists.no_results') }}</p>
                </div>
            @else
                <div class="max-w-full overflow-x-auto">
                    <table class="datatable min-w-full">
                        <thead>
                            <tr>
                                <th><p>Text</p></th>
                                <th><p>{{ __('example::lists.category') }}</p></th>
                                <th class="datatable-col-actions"><p>{{ __('example::lists.actions') }}</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->examples as $example)
                                <tr wire:key="trash-{{ $example->id }}">
                                    <td>
                                        <p>{{ $example->text }}</p>
                                        @if ($example->deleted_at)
                                            <p class="text-xs text-gray-500">{{ __('example::lists.deleted_at', ['date' => $example->deleted_at->diffForHumans()]) }}</p>
                                        @endif
                                    </td>
                                    <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                                    <td>
                                        <div class="text-center">
                                            @if ($view === 'trash')
                                                <button
                                                    class="btn-icon success has-tooltip group"
                                                    type="button"
                                                    wire:click="restore('{{ $example->delete_token }}')"
                                                >
                                                    <span class="ph ph-arrow-counter-clockwise"></span>
                                                    <span class="tooltip">{{ __('example::lists.restore') }}</span>
                                                </button>
                                                <button
                                                    class="btn-icon danger has-tooltip group"
                                                    type="button"
                                                    data-title="{{ __('example::lists.force_delete_title') }}"
                                                    data-message="{{ __('example::lists.force_delete_message', ['name' => $example->text]) }}"
                                                    data-confirm="{{ __('example::lists.force_delete') }}"
                                                    data-cancel="{{ __('example::labels.cancel') }}"
                                                    data-token="{{ $example->delete_token }}"
                                                    data-component="{{ $this->getId() }}"
                                                    @click="$dispatch('confirm-dialog', {
                                                        title: $el.dataset.title,
                                                        message: $el.dataset.message,
                                                        confirmText: $el.dataset.confirm,
                                                        cancelText: $el.dataset.cancel,
                                                        confirmColor: 'danger',
                                                        icon: 'ph ph-warning',
                                                        wireMethod: 'forceDelete',
                                                        wireParams: [$el.dataset.token],
                                                        wireComponent: $el.dataset.component
                                                    })"
                                                >
                                                    <span class="ph ph-x-circle"></span>
                                                    <span class="tooltip">{{ __('example::lists.force_delete') }}</span>
                                                </button>
                                            @else
                                                <button
                                                    class="btn-icon danger has-tooltip group"
                                                    type="button"
                                                    wire:click="delete('{{ $example->delete_token }}')"
                                                >
                                                    <span class="ph ph-trash"></span>
                                                    <span class="tooltip">{{ __('example::labels.delete') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="pagination-container p-3">{{ $this->examples->links() }}</div>
    </x-synapse-panel>
</div>
