<div x-data="{ pageName: 'Sortable', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.sortable')">
        <x-slot:toolbar>
            <div class="w-64">
                <x-synapse-adv-select
                    wire-model="categoryId"
                    :live="true"
                    :options="$this->categories"
                    :placeholder="__('example::lists.category')"
                />
            </div>
        </x-slot:toolbar>

        <div class="syn-panel-body px-5 py-4 sm:px-6">
            @if ($this->items->isEmpty())
                <div class="syn-empty-state">
                    <i class="ph ph-list syn-empty-state-icon"></i>
                    <p class="syn-empty-state-text">{{ __('example::lists.choose_category') }}</p>
                </div>
            @else
                <ul
                    class="space-y-2"
                    wire:sort="moveItem"
                >
                    @foreach ($this->items as $example)
                        <li
                            class="card flex items-center gap-3 px-4 py-3"
                            wire:key="sortable-{{ $example->id }}"
                            wire:sort:item="{{ $example->id }}"
                        >
                            <span
                                class="cursor-grab text-gray-400"
                                wire:sort:handle
                                aria-hidden="true"
                            ><i class="ph ph-dots-six-vertical"></i></span>
                            <span class="flex-1">{{ $example->text }}</span>
                            <x-synapse-badge
                                :color="$example->status->color()"
                                size="sm"
                            >{{ $example->status->label() }}</x-synapse-badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-synapse-panel>
</div>
