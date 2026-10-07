<div x-data="{ pageName: 'Example List', isHome: false, drawerOpen: false }">
    @include('synapps::components.layouts.partials.loader')
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <div class="space-y-5 sm:space-y-6">
        <x-synapse-panel :title="__('example::labels.example_list')">
            <x-slot:toolbar>
                @canAccess('example.manage.create')
                <a
                    class="btn primary"
                    :href="$withBack('{{ backend_route('example.form') }}')"
                    wire:navigate
                >
                    <span class="ph ph-plus"></span>
                    {{ __('example::labels.add') }}
                </a>
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
                                        <p>Text</p>
                                        <x-synapse-sort-icon
                                            field="text"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th><p>{{ __('example::labels.category') }}</p></th>
                                <th class="sortable">
                                    <div>
                                        <p>{{ __('example::labels.status') }}</p>
                                        <x-synapse-sort-icon
                                            field="status"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th><p>Email</p></th>
                                <th><p>Number</p></th>
                                <th><p>Textarea</p></th>
                                <th class="sortable">
                                    <div>
                                        <p>Options</p>
                                        <x-synapse-sort-icon
                                            field="dropdown"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div>
                                        <p>Date Time</p>
                                        <x-synapse-sort-icon
                                            field="datetime"
                                            :sort-field="$sortField"
                                            :sort-direction="$sortDirection"
                                        />
                                    </div>
                                </th>
                                <th class="datatable-col-actions"><p>{{ __('example::labels.actions') }}</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->exampleList as $list)
                                <tr wire:key="example-{{ $list->id }}">
                                    <td><p>{{ $this->exampleList->firstItem() + $loop->index }}</p></td>
                                    <td><p><a class="hover:underline" href="{{ backend_route('example.show', ['id' => $list->id]) }}" wire:navigate>{{ $list->text }}</a></p></td>
                                    <td>
                                        @if ($list->category)
                                            <button
                                                type="button"
                                                title="Click to filter by this category"
                                                class="{{ in_array($list->category_id, $filterCategories)
                                                    ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-brand-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-brand-900/20' }} inline-flex cursor-pointer items-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium transition-colors"
                                                wire:click="filterByCategory({{ $list->category_id }})"
                                            >
                                                <span class="ph ph-folder text-xs"></span>
                                                <span>{{ $list->category->name }}</span>
                                            </button>
                                        @else
                                            <span class="text-sm italic text-gray-400 dark:text-gray-600">No category</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-synapse-badge
                                            :color="$list->status->color()"
                                            size="sm"
                                        >{{ $list->status->label() }}</x-synapse-badge>
                                    </td>
                                    <td><p>{{ $list->email }}</p></td>
                                    <td><p>{{ $list->number }}</p></td>
                                    <td><p>{{ Str::limit($list->textarea, 50) }}</p></td>
                                    <td><p>{{ $list->dropdown + 1 }}</p></td>
                                    <td><p>{{ $list->datetime }}</p></td>
                                    <td>
                                        <div class="text-center">
                                            @canAccess('example.manage.update')
                                            <a
                                                class="btn-icon warning has-tooltip group"
                                                :href="$withBack('{{ backend_route('example.form', ['id' => $list->id]) }}')"
                                                wire:navigate
                                            >
                                                <span class="ph ph-pencil-simple"></span>
                                                <span class="tooltip">{{ __('example::labels.edit') }}</span>
                                            </a>
                                            @endcanAccess
                                            @canAccess('example.manage.delete')
                                            <button
                                                class="btn-icon danger has-tooltip group"
                                                type="button"
                                                data-title="Delete Example"
                                                data-message="Are you sure you want to delete &quot;{{ $list->text }}&quot;? It will be moved to the trash."
                                                data-confirm="{{ __('example::labels.yes_delete') }}"
                                                data-cancel="{{ __('example::labels.cancel') }}"
                                                data-token="{{ $list->delete_token }}"
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

            @if ($this->exampleList->isEmpty())
                @include('example::partials.empty-state', $this->hasFilters
                    ? ['icon' => 'ph ph-magnifying-glass', 'title' => __('example::pages.no_results'), 'text' => __('example::pages.no_results_text'), 'action' => ['label' => __('example::pages.clear_filters'), 'click' => 'clearFilters']]
                    : ['icon' => 'ph ph-cube', 'title' => __('example::pages.no_examples'), 'text' => __('example::pages.no_examples_text'), 'action' => ['label' => __('example::pages.create_example'), 'href' => backend_route('example.editor')]])
            @endif

            <x-synapse-per-page-selector :paginator="$this->exampleList" />
        </x-synapse-panel>
    </div>

    <x-synapse-drawer :title="$this->drawerTitle">
        <div class="space-y-6">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Dropdown Option</label>
                <x-synapse-adv-select
                    wire-model="filterOption"
                    :live="true"
                    :options="$this->options"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('example::labels.category') }}</label>
                <x-synapse-adv-select
                    wire-model="filterCategories"
                    :live="true"
                    :options="$this->categories"
                    :multiple="true"
                    placeholder="Select categories"
                />
            </div>
        </div>
    </x-synapse-drawer>
</div>
