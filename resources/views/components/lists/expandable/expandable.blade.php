<div x-data="{ pageName: 'Expandable rows', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::lists.expandable')">
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th class="datatable-col-checkbox"><p></p></th>
                            <th><p>Text</p></th>
                            <th><p>{{ __('example::labels.status') }}</p></th>
                            <th><p>{{ __('example::lists.category') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->examples as $example)
                            <tr
                                class="cursor-pointer"
                                wire:key="row-{{ $example->id }}"
                                wire:click="toggle({{ $example->id }})"
                                aria-expanded="{{ $expandedId === $example->id ? 'true' : 'false' }}"
                            >
                                <td><i @class(['ph', 'ph-caret-down' => $expandedId === $example->id, 'ph-caret-right' => $expandedId !== $example->id])></i></td>
                                <td><p>{{ $example->text }}</p></td>
                                <td>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </td>
                                <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                            </tr>
                            @if ($expandedId === $example->id)
                                <tr
                                    class="syn-row-muted"
                                    wire:key="detail-{{ $example->id }}"
                                >
                                    <td colspan="4">
                                        <div class="grid gap-4 p-2 md:grid-cols-3">
                                            <div class="md:col-span-2">
                                                <p class="syn-field-caption">{{ __('example::lists.content') }}</p>
                                                <p class="text-sm">{{ Str::limit(strip_tags((string) $example->content), 400) }}</p>
                                            </div>
                                            <div class="space-y-3">
                                                <div>
                                                    <p class="syn-field-caption">{{ __('example::lists.meta') }}</p>
                                                    <dl class="text-sm">
                                                        @foreach ($example->meta ?? [] as $key => $value)
                                                            <div class="flex gap-2"><dt class="font-medium">{{ $key }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value) }}</dd></div>
                                                        @endforeach
                                                    </dl>
                                                </div>
                                                <div>
                                                    <p class="syn-field-caption">{{ __('example::lists.tags_label') }}</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($example->tags as $tag)
                                                            <x-synapse-badge size="sm">{{ $tag->name }}</x-synapse-badge>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container p-3">{{ $this->examples->links() }}</div>
    </x-synapse-panel>
</div>
