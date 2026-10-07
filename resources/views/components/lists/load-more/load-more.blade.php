<div x-data="{ pageName: 'Load more', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.load_more')">
        <div class="syn-panel-body space-y-3 px-5 py-4 sm:px-6">
            <p class="text-sm text-gray-500">{{ __('example::lists.showing', ['shown' => $this->examples->count(), 'total' => $this->total]) }}</p>

            <ul class="space-y-2">
                @foreach ($this->examples as $example)
                    <li
                        class="card flex items-center justify-between gap-3 px-4 py-3"
                        wire:key="more-{{ $example->id }}"
                    >
                        <span>{{ $example->text }}</span>
                        <x-synapse-badge
                            :color="$example->status->color()"
                            size="sm"
                        >{{ $example->status->label() }}</x-synapse-badge>
                    </li>
                @endforeach
            </ul>

            @if ($this->hasMore)
                <div
                    class="flex justify-center pt-2"
                    wire:intersect="loadMore"
                >
                    <button
                        class="btn secondary"
                        type="button"
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                    >{{ __('example::lists.load_more_button') }}</button>
                </div>
            @else
                <p class="pt-2 text-center text-sm text-gray-500">{{ __('example::lists.end_of_list') }}</p>
            @endif
        </div>
    </x-synapse-panel>
</div>
