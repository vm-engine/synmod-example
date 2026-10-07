<div x-data="{ pageName: 'Kanban', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-kanban sort-method="moveItem">
        @foreach ($this->statuses as $status)
            <x-synapse-kanban.column
                :id="$status->value"
                :label="$status->label()"
                :color="$status->color()"
                :count="$this->counts[$status->value]"
                wire:key="column-{{ $status->value }}"
            >
                @if ($this->wipLimit > 0 && $this->counts[$status->value] > $this->wipLimit)
                    <div wire:sort:ignore>
                        <x-synapse-badge
                            color="danger"
                            size="sm"
                            icon="ph ph-warning"
                        >{{ __('example::pages.over_limit', ['limit' => $this->wipLimit]) }}</x-synapse-badge>
                    </div>
                @endif

                @foreach ($this->columns[$status->value] as $example)
                    <x-synapse-kanban.card
                        :id="$example->id"
                        wire:key="card-{{ $example->id }}"
                    >
                        <a
                            class="block font-medium hover:underline"
                            href="{{ backend_route('example.show', ['id' => $example->id]) }}"
                            wire:navigate
                            wire:sort:ignore
                        >{{ $example->text }}</a>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $example->category?->name ?? __('example::pages.no_category') }}
                            @if ($example->due_at)
                                · {{ $example->due_at->toDateString() }}
                            @endif
                        </p>
                        @if ($example->tags->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($example->tags as $tag)
                                    <x-synapse-badge size="sm">{{ $tag->name }}</x-synapse-badge>
                                @endforeach
                            </div>
                        @endif
                    </x-synapse-kanban.card>
                @endforeach

                @if ($this->counts[$status->value] > $this->columns[$status->value]->count())
                    <a
                        class="block py-2 text-center text-xs text-gray-500 hover:underline"
                        href="{{ backend_route('example.lists.inline-filters', ['filterStatus' => $status->value]) }}"
                        wire:navigate
                        wire:sort:ignore
                    >{{ __('example::pages.more_cards', ['count' => $this->counts[$status->value] - $this->columns[$status->value]->count()]) }}</a>
                @endif
            </x-synapse-kanban.column>
        @endforeach
    </x-synapse-kanban>

    <x-synapse-modal
        name="publish-note"
        maxWidth="md"
    >
        <form wire:submit="confirmPublish">
            <div class="syn-modal-header">
                <h3 class="syn-modal-title">{{ __('example::pages.publish_note') }}</h3>
            </div>
            <div class="syn-modal-body space-y-2">
                <p class="text-sm text-gray-500">{{ __('example::pages.publish_note_intro') }}</p>
                <textarea
                    class="form-input"
                    rows="3"
                    maxlength="500"
                    aria-label="{{ __('example::pages.publish_note') }}"
                    wire:model="publishNote"
                ></textarea>
                @error('publishNote')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="syn-modal-footer flex justify-end gap-2">
                <button
                    class="btn secondary"
                    type="button"
                    wire:click="cancelPublish"
                >{{ __('example::pages.cancel') }}</button>
                <button
                    class="btn primary"
                    type="submit"
                >{{ __('example::pages.publish') }}</button>
            </div>
        </form>
    </x-synapse-modal>
</div>
