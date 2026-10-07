<div x-data="{ pageName: 'Nodes', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <x-synapse-panel :title="__('example::lists.nodes')">
        <x-slot:toolbar>
            @canAccess('example.node.create')
            <button
                class="btn primary"
                type="button"
                wire:click="startAdd('root')"
            ><span class="ph ph-plus"></span> {{ __('example::lists.add_root') }}</button>
            @endcanAccess
        </x-slot:toolbar>

        <div class="syn-panel-body px-5 py-4 sm:px-6">
            @if ($addingTo === 'root')
                <div class="mb-3 flex items-center gap-2">
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

            @include('example::partials.node-branch', ['parentKey' => 'root', 'tree' => $this->childrenByParent, 'component' => $this])
        </div>
    </x-synapse-panel>
</div>
