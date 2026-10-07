<div x-data="{ pageName: 'Grouped table', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.grouped')">
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>Text</p></th>
                            <th><p>{{ __('example::lists.category') }}</p></th>
                            <th><p>Email</p></th>
                        </tr>
                    </thead>
                    @foreach ($this->groups as $group)
                        <tbody
                            wire:key="group-{{ $group['status']->value }}"
                            x-data="{ open: true }"
                        >
                            <tr class="syn-row-muted">
                                <td colspan="3">
                                    <button
                                        class="btn secondary"
                                        type="button"
                                        :aria-expanded="open ? 'true' : 'false'"
                                        @click="open = !open"
                                    >
                                        <i
                                            class="ph ph-caret-down"
                                            :class="{ '-rotate-90': !open }"
                                        ></i>
                                        <x-synapse-badge :color="$group['status']->color()">{{ $group['status']->label() }}</x-synapse-badge>
                                        <span class="text-sm text-gray-500">{{ $group['items']->count() }}</span>
                                    </button>
                                </td>
                            </tr>
                            @forelse ($group['items'] as $example)
                                <tr
                                    wire:key="grouped-{{ $example->id }}"
                                    x-show="open"
                                >
                                    <td><p>{{ $example->text }}</p></td>
                                    <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                                    <td><p>{{ $example->email }}</p></td>
                                </tr>
                            @empty
                                <tr x-show="open">
                                    <td colspan="3"><p class="text-sm text-gray-500">{{ __('example::lists.group_empty') }}</p></td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>
    </x-synapse-panel>
</div>
