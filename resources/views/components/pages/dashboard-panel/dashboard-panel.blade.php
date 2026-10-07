<div class="syn-panel-body">
    @if ($this->items->isEmpty())
        @include('example::partials.empty-state', ['icon' => 'ph ph-tray', 'title' => __('example::pages.nothing_here'), 'text' => null, 'action' => null])
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($this->items as $example)
                <li
                    class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6"
                    wire:key="panel-{{ $kind }}-{{ $example->id }}"
                >
                    <a
                        class="truncate hover:underline"
                        href="{{ backend_route('example.show', ['id' => $example->id]) }}"
                        wire:navigate
                    >{{ $example->text }}</a>
                    <span class="shrink-0 text-xs text-gray-500">
                        {{ $kind === 'due-soon' ? $example->due_at?->toDateString() : $example->updated_at->diffForHumans() }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
