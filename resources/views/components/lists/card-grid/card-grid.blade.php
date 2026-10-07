<div x-data="{ pageName: 'Card grid', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.card_grid')">
        <div class="syn-panel-body space-y-5 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @class(['btn', 'primary' => $status === 'all', 'secondary' => $status !== 'all'])
                    wire:click="setStatus('all')"
                >{{ __('example::lists.all') }}</button>
                @foreach (\VmEngine\Example\Enums\ExampleStatus::cases() as $case)
                    <button
                        type="button"
                        wire:key="chip-{{ $case->value }}"
                        @class(['btn', 'primary' => $status === $case->value, 'secondary' => $status !== $case->value])
                        wire:click="setStatus('{{ $case->value }}')"
                    >
                        {{ $case->label() }}
                        <x-synapse-badge size="sm">{{ $this->statusCounts[$case->value] }}</x-synapse-badge>
                    </button>
                @endforeach
            </div>

            @if ($this->examples->isEmpty())
                <div class="syn-empty-state">
                    <i class="ph ph-cards syn-empty-state-icon"></i>
                    <p class="syn-empty-state-text">{{ __('example::lists.no_results') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($this->examples as $example)
                        <article
                            class="card"
                            wire:key="card-{{ $example->id }}"
                        >
                            <div class="card-body space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-white/90">{{ $example->text }}</h4>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <i class="ph ph-folder"></i> {{ $example->category?->name ?? '—' }}
                                    ·
                                    {{ $example->due_at ? __('example::lists.due', ['date' => $example->due_at->toDateString()]) : __('example::lists.no_due') }}
                                </p>
                                @if ($example->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($example->tags as $tag)
                                            <x-synapse-badge size="sm">{{ $tag->name }}</x-synapse-badge>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-container">{{ $this->examples->links() }}</div>
            @endif
        </div>
    </x-synapse-panel>
</div>
