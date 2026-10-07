<div x-data="{ pageName: 'Bulk actions', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <x-synapse-panel :title="__('example::lists.bulk_actions')">
        <x-slot:toolbar>
            <x-synapse-search-box
                :placeholder="__('example::labels.search_placeholder')"
                wire:model.live.debounce="q"
            />
            <select
                class="form-input"
                wire:model.live="filterStatus"
                aria-label="{{ __('example::lists.filter_status') }}"
            >
                <option value="all">{{ __('example::lists.all') }}</option>
                @foreach (\VmEngine\Example\Enums\ExampleStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
        </x-slot:toolbar>

        @if ($this->selectedCount > 0)
            <div class="syn-selection-bar-card mx-5 mb-3 flex flex-wrap items-center gap-3 p-3 sm:mx-6">
                <span class="syn-selection-bar-count">{{ __('example::lists.selected', ['count' => $this->selectedCount]) }}</span>
                @unless ($selectAllMatching)
                    <button
                        class="btn secondary"
                        type="button"
                        wire:click="selectAllMatchingRows"
                    >{{ __('example::lists.select_all_matching', ['count' => $this->examples->total()]) }}</button>
                @endunless
                @canAccess('example.manage.update')
                @foreach (\VmEngine\Example\Enums\ExampleStatus::cases() as $case)
                    <button
                        class="btn secondary"
                        type="button"
                        wire:key="bulk-status-{{ $case->value }}"
                        wire:click="bulkSetStatus('{{ $case->value }}')"
                    >{{ __('example::lists.set_status') }}: {{ $case->label() }}</button>
                @endforeach
                @endcanAccess
                @canAccess('example.manage.delete')
                <button
                    class="btn danger"
                    type="button"
                    data-title="{{ __('example::lists.bulk_delete_title') }}"
                    data-message="{{ __('example::lists.bulk_delete_message', ['count' => $this->selectedCount]) }}"
                    data-confirm="{{ __('example::labels.yes_delete') }}"
                    data-cancel="{{ __('example::labels.cancel') }}"
                    data-component="{{ $this->getId() }}"
                    @click="$dispatch('confirm-dialog', {
                        title: $el.dataset.title,
                        message: $el.dataset.message,
                        confirmText: $el.dataset.confirm,
                        cancelText: $el.dataset.cancel,
                        confirmColor: 'danger',
                        icon: 'ph ph-trash',
                        wireMethod: 'bulkDelete',
                        wireParams: [],
                        wireComponent: $el.dataset.component
                    })"
                ><span class="ph ph-trash"></span> {{ __('example::lists.bulk_delete') }}</button>
                @endcanAccess
                <button
                    class="btn"
                    type="button"
                    wire:click="clearSelection"
                >{{ __('example::lists.clear_selection') }}</button>
            </div>
        @endif

        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th class="datatable-col-checkbox">
                                <input
                                    class="form-checkbox"
                                    type="checkbox"
                                    title="{{ __('example::lists.select_page') }}"
                                    aria-label="{{ __('example::lists.select_page') }}"
                                    wire:click="{{ count($selected) > 0 || $selectAllMatching ? 'clearSelection' : 'selectPage' }}"
                                    @checked(count($selected) > 0 || $selectAllMatching)
                                >
                            </th>
                            <th><p>Text</p></th>
                            <th><p>{{ __('example::labels.status') }}</p></th>
                            <th><p>{{ __('example::lists.category') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->examples as $example)
                            <tr
                                wire:key="bulk-{{ $example->id }}"
                                @class(['syn-row-selected' => $selectAllMatching || in_array($example->id, $selected, true)])
                            >
                                <td>
                                    <input
                                        class="form-checkbox"
                                        type="checkbox"
                                        value="{{ $example->id }}"
                                        aria-label="{{ $example->text }}"
                                        wire:model.live="selected"
                                        @disabled($selectAllMatching)
                                    >
                                </td>
                                <td><p>{{ $example->text }}</p></td>
                                <td>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </td>
                                <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container p-3">{{ $this->examples->links() }}</div>
    </x-synapse-panel>
</div>
