<div x-data="{ pageName: 'Category List', isHome: false, drawerOpen: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <!-- Confirmation Dialog -->
    <x-synapse-confirm-dialog />

    <!-- Category List -->
    <div class="space-y-5 sm:space-y-6">

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="flex justify-between">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ __('example::labels.category_list') }}
                    </h3>
                </div>
                <div class="my-auto flex gap-3 px-5">
                    @canAccess('example.category.create')
                    <div>
                        <button
                            class="btn primary"
                            type="button"
                            @click="$dispatch('open-modal-category-form')"
                            wire:click="$dispatch('reset-category-form')"
                        >
                            <span class="fa-solid fa-plus"></span>
                            {{ __('example::labels.add') }}
                        </button>
                    </div>
                    @endcanAccess
                    <div class="input-group">
                        <button
                            class="input-group-item right btn"
                            type="button"
                            title="{{ __('example::labels.advanced_filter') }}"
                            @click="drawerOpen = true"
                        ><span class="fa-solid fa-filter"></span></button>
                        <input
                            class="form-input p-2"
                            id="q"
                            name="q"
                            type="text"
                            placeholder="{{ __('example::labels.search_placeholder') }}"
                            wire:model.live.debounce="q"
                        >
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto">
                    <table
                        class="datatable min-w-full"
                        id="dataTable"
                        width="100%"
                        cellspacing="0"
                    >
                        <thead>
                            <tr>
                                <th>
                                    <p>{{ __('example::labels.no') }}</p>
                                </th>
                                <th class="sortable">
                                    <div>
                                        <p>{{ __('example::labels.name') }}</p>
                                        <a
                                            href="#"
                                            wire:click.prevent="sortData('name')"
                                        >
                                            <span
                                                class="fa fa-solid {{ $sort != 'name' ? 'fa-sort' : ($sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down') }}"
                                            ></span>
                                        </a>
                                    </div>
                                </th>
                                <th>
                                    <p>{{ __('example::labels.slug') }}</p>
                                </th>
                                <th>
                                    <p>{{ __('example::labels.description') }}</p>
                                </th>
                                <th>
                                    <p>{{ __('example::labels.status') }}</p>
                                </th>
                                <th>
                                    <p>{{ __('example::labels.actions') }}</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($this->categoryList as $category)
                                <tr wire:key="category-{{ $category->id }}">
                                    <td>
                                        <div class="flex items-center">
                                            <p>{{ ($this->categoryList->currentPage() - 1) * $limit + $i }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <p class="font-medium">{{ $category->name }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <p class="text-gray-600 dark:text-gray-400">{{ $category->slug }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ Str::limit($category->description, 50) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
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
                                            @if ($category->is_active)
                                                <span class="text-success">Active</span>
                                            @else
                                                <span class="text-danger">Inactive</span>
                                            @endif
                                            @endcanAccess
                                        </div>
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
                                                <span class="fa-solid fa-edit"></span>
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
                                                    icon: 'fa-solid fa-trash',
                                                    wireMethod: 'delete',
                                                    wireParams: [$el.dataset.token],
                                                    wireComponent: $el.dataset.component
                                                })"
                                            >
                                                <span class="fa-solid fa-trash"></span>
                                                <span class="tooltip">{{ __('example::labels.delete') }}</span>
                                            </button>
                                            @endcanAccess
                                        </div>
                                    </td>
                                </tr>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex gap-2 p-3">
                <div class="flex-none p-1">
                    <select
                        class="shadow-theme-sm rounded-sm p-1"
                        id="limit"
                        name="limit"
                        wire:model.live="limit"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="pagination-container flex-1 grow">
                    {{ $this->categoryList->links() }}
                </div>
            </div>
        </div>
    </div>

    @php
        $advanceTitle = '<span class="fa-solid fa-filter mr-2"></span>' . __('example::labels.advanced_filter');
    @endphp
    <x-synapse-drawer :title="$advanceTitle">
        <div class="mb-6">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('example::labels.status_filter') }}
            </label>
            <x-synapse-select
                id="filterStatus"
                name="filterStatus"
                wire:model.live="filterStatus"
                :options="$this->statusOptions"
            >
            </x-synapse-select>
        </div>
    </x-synapse-drawer>

    <!-- Category Form Modal -->
    <x-synapse-modal
        name="category-form"
        maxWidth="2xl"
    >
        <livewire:example::category-form />
    </x-synapse-modal>
</div>
