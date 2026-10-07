<div x-data="{ pageName: 'Node browser', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-split-pane
        aside-width="18rem"
        resizable
        storage-key="example-node-browser"
    >
        <x-slot:aside>
            <x-synapse-panel :title="__('example::menu.be.nodes')">
                <div class="syn-panel-body px-3 py-3">
                    @include('example::partials.node-browse-branch', ['nodes' => $this->childrenMap[0] ?? [], 'childrenMap' => $this->childrenMap, 'selectedId' => $node])
                </div>
            </x-synapse-panel>
        </x-slot:aside>

        <x-synapse-panel :title="$node ? $name : __('example::pages.node_browser')">
            @if ($node === null)
                @include('example::partials.empty-state', [
                    'icon' => 'ph ph-tree-structure',
                    'title' => __('example::pages.select_node'),
                    'text' => __('example::pages.select_node_text'),
                    'action' => null,
                ])
            @else
                <div class="syn-panel-body space-y-4 px-5 py-4 sm:px-6">
                    <nav class="flex flex-wrap items-center gap-1 text-xs text-gray-500">
                        @foreach ($this->path() as $crumb)
                            <button
                                class="hover:underline"
                                type="button"
                                wire:key="crumb-{{ $crumb->id }}"
                                wire:click="selectNode({{ $crumb->id }})"
                            >{{ $crumb->name }}</button>
                            @unless ($loop->last)
                                <i class="ph ph-caret-right"></i>
                            @endunless
                        @endforeach
                    </nav>

                    <form
                        class="space-y-4"
                        wire:submit="save"
                    >
                        <div class="space-y-1">
                            <label
                                class="form-label"
                                for="node-name"
                            >{{ __('example::pages.node_name') }}</label>
                            <input
                                class="form-input"
                                id="node-name"
                                type="text"
                                maxlength="100"
                                wire:model="name"
                            >
                            @error('name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1">
                            <label
                                class="form-label"
                                for="node-description"
                            >{{ __('example::pages.node_description') }}</label>
                            <textarea
                                class="form-input"
                                id="node-description"
                                rows="4"
                                maxlength="1000"
                                wire:model="description"
                            ></textarea>
                            @error('description')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <button
                            class="btn primary"
                            type="submit"
                        >{{ __('example::pages.save') }}</button>
                    </form>

                    <div>
                        <h4 class="text-sm font-semibold">{{ __('example::pages.children') }}</h4>
                        <ul class="mt-2 space-y-1 text-sm">
                            @forelse ($this->childrenMap[$node] ?? [] as $childNode)
                                <li wire:key="child-{{ $childNode->id }}">
                                    <button
                                        class="hover:underline"
                                        type="button"
                                        wire:click="selectNode({{ $childNode->id }})"
                                    >{{ $childNode->name }}</button>
                                </li>
                            @empty
                                <li class="text-gray-500">{{ __('example::pages.no_children') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif
        </x-synapse-panel>
    </x-synapse-split-pane>
</div>
