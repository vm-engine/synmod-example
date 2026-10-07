<div x-data="{ pageName: 'Inline filters', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.inline_filters')">
        <div class="syn-panel-body px-5 py-4 sm:px-6">
            @include('example::partials.example-filter-row', ['statuses' => $this->statuses, 'categories' => $this->categories])
        </div>
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>Text</p></th>
                            <th><p>{{ __('example::labels.status') }}</p></th>
                            <th><p>{{ __('example::lists.category') }}</p></th>
                            @if ($this->showDueColumn)
                                <th><p>Due</p></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->examples as $example)
                            <tr wire:key="filtered-{{ $example->id }}">
                                <td><p>{{ $example->text }}</p></td>
                                <td>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </td>
                                <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                                @if ($this->showDueColumn)
                                    <td><p>{{ $example->due_at?->toDateString() ?? '—' }}</p></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $this->showDueColumn ? 4 : 3 }}"><p class="py-6 text-center text-sm text-gray-500">{{ __('example::lists.no_results') }}</p></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-synapse-per-page-selector :paginator="$this->examples" />
    </x-synapse-panel>
</div>
