<div>
    @if ($this->related->isEmpty())
        <p class="py-6 text-center text-sm text-gray-500">{{ __('example::pages.no_related') }}</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($this->related as $example)
                <li
                    class="flex items-center justify-between gap-3 py-2"
                    wire:key="related-{{ $example->id }}"
                >
                    <a
                        class="truncate hover:underline"
                        href="{{ backend_route('example.show', ['id' => $example->id]) }}"
                        wire:navigate
                    >{{ $example->text }}</a>
                    <x-synapse-badge
                        :color="$example->status->color()"
                        size="sm"
                    >{{ $example->status->label() }}</x-synapse-badge>
                </li>
            @endforeach
        </ul>
    @endif
</div>
