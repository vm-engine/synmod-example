<div x-data="{ pageName: 'Category List', isHome: false, drawerOpen: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <div class="space-y-5 sm:space-y-6">
        <x-synapse-panel :title="__('example::labels.category_list')">
            <x-slot:toolbar>
                @canAccess('example.category.create')
                <button
                    class="btn primary"
                    type="button"
                    @click="$dispatch('open-modal-category-form')"
                    wire:click="$dispatch('reset-category-form')"
                >
                    <span class="ph ph-plus"></span>
                    {{ __('example::labels.add') }}
                </button>
                @endcanAccess
                <x-synapse-search-box
                    :placeholder="__('example::labels.search_placeholder')"
                    wire:model.live.debounce="q"
                >
                    <x-slot:trailing>
                        <button
                            class="btn"
                            type="button"
                            title="{{ __('example::labels.advanced_filter') }}"
                            @click="drawerOpen = true"
                        ><span class="ph ph-funnel"></span></button>
                    </x-slot:trailing>
                </x-synapse-search-box>
            </x-slot:toolbar>

            <div class="syn-panel-body">
                <div class="max-w-full overflow-x-auto">
                    <table class="datatable min-w-full">
                        <thead>
                            <tr>
                                <th><p>{{ __('example::labels.no') }}</p></th>
                                <th class="sortable">
                                    <div>
                                        <p>{{ __('example::labels.name') }}</p>
                                        <x-synapse-sort-icon
                                            field="name"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th><p>{{ __('example::labels.slug') }}</p></th>
                                <th><p>{{ __('example::labels.description') }}</p></th>
                                <th class="sortable">
                                    <div>
                                        <p>{{ __('example::labels.status') }}</p>
                                        <x-synapse-sort-icon
                                            field="is_active"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th class="datatable-col-actions"><p>{{ __('example::labels.actions') }}</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->categoryList as $category)
                                <tr wire:key="category-{{ $category->id }}">
                                    <td><p>{{ $this->categoryList->firstItem() + $loop->index }}</p></td>
                                    <td><p class="font-medium">{{ $category->name }}</p></td>
                                    <td><p class="text-gray-600 dark:text-gray-400">{{ $category->slug }}</p></td>
                                    <td><p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($category->description, 50) }}</p></td>
                                    <td>
                                        @canAccess('example.category.update')
                                        <x-synapse-toggler
                                            wire:key="toggler-{{ $category->id }}"
                                            title="Toggle Status"
                                            :inline="true"
                                            :checked="$category->is_active"
                                            @change="active = !active"
                                            wire:change="toggleActive({{ $category->id }})"
                                            activeColor="green"
                                        />
                                    @else
                                        <x-synapse-badge
                                            :color="$category->is_active ? 'success' : 'gray'"
                                            size="sm"
                                        >{{ $category->is_active ? __('example::labels.active') : __('example::labels.inactive') }}</x-synapse-badge>
                                        @endcanAccess
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            @canAccess('example.category.update')
                                            <button
                                                class="btn-icon warning has-tooltip group"
                                                type="button"
                                                @click="$dispatch('open-modal-category-form')"
                                                wire:click="$dispatch('load-category', { id: {{ $category->id }} })"
                                            >
                                                <span class="ph ph-pencil-simple"></span>
                                                <span class="tooltip">{{ __('example::labels.edit') }}</span>
                                            </button>
                                            @endcanAccess
                                            @canAccess('example.category.delete')
                                            <button
                                                class="btn-icon danger has-tooltip group"
                                                type="button"
                                                data-title="{{ __('example::labels.delete_category_title') }}"
                                                data-message="{{ __('example::labels.delete_category_message', ['name' => $category->name]) }}"
                                                data-confirm="{{ __('example::labels.yes_delete') }}"
                                                data-cancel="{{ __('example::labels.cancel') }}"
                                                data-token="{{ $category->delete_token }}"
                                                data-component="{{ $this->getId() }}"
                                                @click="$dispatch('confirm-dialog', {
                                                    title: $el.dataset.title,
                                                    message: $el.dataset.message,
                                                    confirmText: $el.dataset.confirm,
                                                    cancelText: $el.dataset.cancel,
                                                    confirmColor: 'danger',
                                                    icon: 'ph ph-trash',
                                                    wireMethod: 'delete',
                                                    wireParams: [$el.dataset.token],
                                                    wireComponent: $el.dataset.component
                                                })"
                                            >
                                                <span class="ph ph-trash"></span>
                                                <span class="tooltip">{{ __('example::labels.delete') }}</span>
                                            </button>
                                            @endcanAccess
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <x-synapse-per-page-selector :paginator="$this->categoryList" />
        </x-synapse-panel>
    </div>

    <x-synapse-drawer :title="$this->drawerTitle">
        <div class="mb-6">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('example::labels.status_filter') }}</label>
            <x-synapse-adv-select
                wire-model="filterStatus"
                :live="true"
                :options="$this->statusOptions"
            />
        </div>
    </x-synapse-drawer>

    <x-synapse-modal
        name="category-form"
        maxWidth="2xl"
    >
        <livewire:example::category-form />
    </x-synapse-modal>
</div>
