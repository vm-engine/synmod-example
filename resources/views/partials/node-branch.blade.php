<ul
    class="space-y-1"
    wire:sort="moveNode"
    wire:sort:group="nodes"
    wire:sort:group-id="{{ $parentKey }}"
>
    @foreach ($tree->get($parentKey, []) as $node)
        <li
            wire:key="node-{{ $node->id }}"
            wire:sort:item="{{ $node->id }}"
            x-data="{ open: true }"
        >
            <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-white/5">
                <span
                    class="cursor-grab text-gray-400"
                    wire:sort:handle
                    aria-hidden="true"
                ><i class="ph ph-dots-six-vertical"></i></span>

                @if ($tree->has((string) $node->id))
                    <button
                        class="btn-icon"
                        type="button"
                        :aria-expanded="open ? 'true' : 'false'"
                        @click="open = !open"
                        wire:sort:ignore
                    ><i
                            class="ph ph-caret-down"
                            :class="{ '-rotate-90': !open }"
                        ></i></button>
                @else
                    <span class="inline-block w-8"></span>
                @endif

                @if ($component->renamingId === $node->id)
                    <div
                        class="flex flex-1 items-center gap-2"
                        wire:sort:ignore
                    >
                        <input
                            class="form-input"
                            type="text"
                            maxlength="100"
                            aria-label="{{ __('example::lists.node_name') }}"
                            wire:model="renameValue"
                            wire:keydown.enter="saveRename"
                            wire:keydown.escape="cancelInline"
                        >
                        <button
                            class="btn primary"
                            type="button"
                            wire:click="saveRename"
                        >{{ __('example::lists.save') }}</button>
                    </div>
                @else
                    <span class="flex-1">{{ $node->name }}</span>
                    <div
                        class="flex gap-1"
                        wire:sort:ignore
                    >
                        @canAccess('example.node.create')
                        <button
                            class="btn-icon success has-tooltip group"
                            type="button"
                            wire:click="startAdd('{{ $node->id }}')"
                        ><span class="ph ph-plus"></span><span class="tooltip">{{ __('example::lists.add_child') }}</span></button>
                        @endcanAccess
                        @canAccess('example.node.update')
                        <button
                            class="btn-icon warning has-tooltip group"
                            type="button"
                            wire:click="startRename({{ $node->id }})"
                        ><span class="ph ph-pencil-simple"></span><span class="tooltip">{{ __('example::lists.rename') }}</span></button>
                        @endcanAccess
                        @canAccess('example.node.delete')
                        <button
                            class="btn-icon danger has-tooltip group"
                            type="button"
                            data-title="{{ __('example::lists.delete_node_title') }}"
                            data-message="{{ __('example::lists.delete_node_message', ['name' => $node->name]) }}"
                            data-confirm="{{ __('example::labels.yes_delete') }}"
                            data-cancel="{{ __('example::labels.cancel') }}"
                            data-token="{{ $node->delete_token }}"
                            data-component="{{ $component->getId() }}"
                            @click="$dispatch('confirm-dialog', {
                                title: $el.dataset.title,
                                message: $el.dataset.message,
                                confirmText: $el.dataset.confirm,
                                cancelText: $el.dataset.cancel,
                                confirmColor: 'danger',
                                icon: 'ph ph-trash',
                                wireMethod: 'delete',
                                wireParams: [$el.dataset.token],
                                wireComponent: $el.dataset.component
                            })"
                        ><span class="ph ph-trash"></span><span class="tooltip">{{ __('example::labels.delete') }}</span></button>
                        @endcanAccess
                    </div>
                @endif
            </div>

            @if ($component->addingTo === (string) $node->id)
                <div
                    class="ml-10 mt-1 flex items-center gap-2"
                    wire:sort:ignore
                >
                    <input
                        class="form-input"
                        type="text"
                        maxlength="100"
                        placeholder="{{ __('example::lists.node_name') }}"
                        wire:model="newName"
                        wire:keydown.enter="addNode"
                        wire:keydown.escape="cancelInline"
                    >
                    <button
                        class="btn primary"
                        type="button"
                        wire:click="addNode"
                    >{{ __('example::lists.save') }}</button>
                    @error('newName')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            @endif

            <div
                class="ml-8 mt-1 border-l border-gray-200 pl-2 dark:border-gray-800"
                x-show="open"
            >
                @include('example::partials.node-branch', ['parentKey' => (string) $node->id, 'tree' => $tree, 'component' => $component])
            </div>
        </li>
    @endforeach
</ul>
