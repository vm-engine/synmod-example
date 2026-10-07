<div x-data="{ pageName: 'Example detail', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])
    <x-synapse-confirm-dialog />
    <x-synapse-lightbox name="example-attachments" />

    @if ($this->example === null)
        <x-synapse-panel :title="__('example::pages.detail')">
            @include('example::partials.empty-state', [
                'icon' => 'ph ph-cube',
                'title' => __('example::pages.no_examples'),
                'text' => __('example::pages.no_examples_text'),
                'action' => ['label' => __('example::pages.create_example'), 'href' => backend_route('example.editor')],
            ])
        </x-synapse-panel>
    @else
        <x-synapse-panel :title="$this->example->text">
            <x-slot:toolbar>
                <div class="flex items-center gap-2">
                    <x-synapse-badge
                        :color="$this->example->status->color()"
                        size="sm"
                    >{{ $this->example->status->label() }}</x-synapse-badge>
                    <a
                        class="btn secondary"
                        href="{{ backend_route('example.editor', ['id' => $this->example->id]) }}"
                        wire:navigate
                    ><span class="ph ph-pencil-simple"></span> {{ __('example::pages.edit') }}</a>
                </div>
            </x-slot:toolbar>

            <div class="syn-panel-body px-5 pt-3 sm:px-6">
                <nav
                    class="syn-tabs"
                    role="tablist"
                >
                    @foreach ($this->tabLabels as $key => $label)
                        <button
                            type="button"
                            role="tab"
                            aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                            wire:key="detail-tab-{{ $key }}"
                            wire:click="selectTab('{{ $key }}')"
                            @class(['syn-tab', 'syn-tab-active' => $tab === $key])
                        >{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            <div class="px-5 py-4 sm:px-6">
                @if ($tab === 'overview')
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-[10rem_1fr]">
                        <dt class="text-gray-500">{{ __('example::pages.category') }}</dt>
                        <dd>{{ $this->example->category?->name ?? __('example::pages.no_category') }}</dd>
                        <dt class="text-gray-500">{{ __('example::pages.email') }}</dt>
                        <dd>{{ $this->example->email }}</dd>
                        <dt class="text-gray-500">{{ __('example::pages.due_at') }}</dt>
                        <dd>{{ $this->example->due_at?->toDateString() ?? '—' }}</dd>
                        <dt class="text-gray-500">{{ __('example::pages.tags') }}</dt>
                        <dd class="flex flex-wrap gap-1">
                            @forelse ($this->example->tags as $tag)
                                <x-synapse-badge size="sm">{{ $tag->name }}</x-synapse-badge>
                            @empty
                                —
                            @endforelse
                        </dd>
                    </dl>

                    @if ($this->example->content)
                        <div class="prose dark:prose-invert mt-6 max-w-none">{!! $this->example->content !!}</div>
                    @endif

                    @if ($this->example->meta)
                        <h4 class="mt-6 text-sm font-semibold">{{ __('example::pages.meta') }}</h4>
                        <table class="datatable mt-2 min-w-full">
                            <tbody>
                                @foreach ($this->example->meta as $key => $value)
                                    <tr wire:key="meta-{{ md5((string) $key) }}">
                                        <td><p class="font-mono">{{ $key }}</p></td>
                                        <td><p>{{ is_scalar($value) ? $value : json_encode($value) }}</p></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @elseif ($tab === 'attachments')
                    <livewire:example::pages.detail-attachments
                        :example-id="$this->example->id"
                        wire:key="detail-attachments-{{ $this->example->id }}"
                        lazy
                    />
                @else
                    <livewire:example::pages.detail-related
                        :example-id="$this->example->id"
                        wire:key="detail-related-{{ $this->example->id }}"
                        lazy
                    />
                @endif
            </div>
        </x-synapse-panel>
    @endif
</div>
