<div x-data="{ pageName: 'Pattern Catalog', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="space-y-5 sm:space-y-6">
        <x-synapse-panel :title="__('example::catalog.title')">
            <div class="syn-panel-body space-y-5 px-5 py-4 sm:px-6 sm:py-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('example::catalog.subtitle') }}</p>

                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('example::catalog.progress', ['built' => $this->progress['built'], 'total' => $this->progress['total']]) }}
                    </p>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="bg-brand-500 h-full rounded-full"
                            style="width: {{ $this->progress['percent'] }}%"
                        ></div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="w-full lg:max-w-sm">
                        <x-synapse-search-box
                            :placeholder="__('example::catalog.search_placeholder')"
                            wire:model.live.debounce="q"
                        />
                    </div>
                    <x-synapse-toggler
                        :label="__('example::catalog.built_only')"
                        wire:model.live="builtOnly"
                    />
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @class(['btn', 'primary' => $group === 'all', 'secondary' => $group !== 'all'])
                        wire:click="setGroup('all')"
                    >{{ __('example::catalog.all') }}</button>
                    @foreach ($this->groupChips as $chip)
                        <button
                            type="button"
                            wire:key="chip-{{ $chip['key'] }}"
                            @class(['btn', 'primary' => $group === $chip['key'], 'secondary' => $group !== $chip['key']])
                            wire:click="setGroup('{{ $chip['key'] }}')"
                        >
                            {{ $chip['label'] }}
                            <x-synapse-badge size="sm">{{ $chip['count'] }}</x-synapse-badge>
                        </button>
                    @endforeach
                </div>

                @if (count($this->patterns) === 0)
                    <div class="syn-empty-state">
                        <i class="ph ph-magnifying-glass syn-empty-state-icon"></i>
                        <p class="syn-empty-state-text">{{ __('example::catalog.empty') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($this->patterns as $pattern)
                            <article
                                wire:key="pattern-{{ $pattern['key'] }}"
                                @class(['card flex flex-col', 'opacity-60' => $pattern['status'] === 'planned'])
                            >
                                <div class="card-body flex flex-1 flex-col gap-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="font-semibold text-gray-800 dark:text-white/90">{{ $pattern['title'] }}</h4>
                                        @if ($pattern['status'] === 'built')
                                            <x-synapse-badge
                                                color="success"
                                                size="sm"
                                            >{{ __('example::catalog.built') }}</x-synapse-badge>
                                        @else
                                            <x-synapse-badge size="sm">{{ __('example::catalog.planned') }}</x-synapse-badge>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pattern['description'] }}</p>

                                    @if ($pattern['sources'])
                                        <ul class="space-y-1">
                                            @foreach ($pattern['sources'] as $source)
                                                <li class="flex items-center gap-1">
                                                    <code class="min-w-0 flex-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $source }}</code>
                                                    <x-synapse-copy-button
                                                        :text="$source"
                                                        :label="__('example::catalog.copy_path')"
                                                    />
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class="mt-auto pt-1">
                                        @if ($pattern['status'] === 'built')
                                            <a
                                                class="btn primary"
                                                href="{{ $this->patternUrl($pattern) }}"
                                                wire:navigate
                                            >
                                                {{ __('example::catalog.open_demo') }}
                                                <i class="ph ph-arrow-right"></i>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('example::catalog.sub_project', ['number' => $pattern['subProject']]) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-synapse-panel>
    </div>
</div>
