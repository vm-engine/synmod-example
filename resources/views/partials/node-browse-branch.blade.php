<ul class="space-y-0.5">
    @foreach ($nodes as $node)
        <li wire:key="browse-node-{{ $node->id }}">
            <button
                type="button"
                wire:click="selectNode({{ $node->id }})"
                @class([
                    'flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-sm',
                    'bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' => $selectedId === $node->id,
                    'hover:bg-gray-50 dark:hover:bg-gray-800' => $selectedId !== $node->id,
                ])
            >
                <i class="ph {{ isset($childrenMap[$node->id]) ? 'ph-folder' : 'ph-file' }}"></i>
                <span class="truncate">{{ $node->name }}</span>
            </button>
            @if (isset($childrenMap[$node->id]))
                <div class="ml-4 border-l border-gray-100 pl-2 dark:border-gray-800">
                    @include('example::partials.node-browse-branch', ['nodes' => $childrenMap[$node->id], 'childrenMap' => $childrenMap, 'selectedId' => $selectedId])
                </div>
            @endif
        </li>
    @endforeach
</ul>
