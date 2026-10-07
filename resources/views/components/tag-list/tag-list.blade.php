<div x-data="{ pageName: 'Tags', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-confirm-dialog />

    <x-synapse-panel :title="__('example::lists.tags')">
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>{{ __('example::lists.tag_name') }}</p></th>
                            <th><p>{{ __('example::lists.tag_color') }}</p></th>
                            <th><p>{{ __('example::lists.tag_examples') }}</p></th>
                            <th class="datatable-col-actions"><p>{{ __('example::lists.actions') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->tags as $tag)
                            <tr wire:key="tag-{{ $tag->id }}">
                                @if ($editingId === $tag->id)
                                    <td>
                                        <input
                                            class="form-input"
                                            type="text"
                                            maxlength="50"
                                            aria-label="{{ __('example::lists.tag_name') }}"
                                            wire:model="editName"
                                            wire:keydown.enter="saveEdit"
                                            wire:keydown.escape="cancelEdit"
                                        >
                                        @error('editName')<p class="form-error">{{ $message }}</p>@enderror
                                    </td>
                                    <td>
                                        <div wire:key="edit-color-{{ $tag->id }}">
                                            <x-synapse-color-picker
                                                wire:model="editColor"
                                                :swatches="$this->swatches"
                                            />
                                        </div>
                                        @error('editColor')<p class="form-error">{{ $message }}</p>@enderror
                                    </td>
                                    <td><p>{{ $tag->examples_count }}</p></td>
                                    <td>
                                        <div class="flex justify-center gap-2">
                                            <button
                                                class="btn primary"
                                                type="button"
                                                wire:click="saveEdit"
                                            >{{ __('example::lists.save') }}</button>
                                            <button
                                                class="btn secondary"
                                                type="button"
                                                wire:click="cancelEdit"
                                            >{{ __('example::lists.cancel') }}</button>
                                        </div>
                                    </td>
                                @else
                                    <td><p class="font-medium">{{ $tag->name }}</p></td>
                                    <td>
                                        <span class="inline-flex items-center gap-2">
                                            <span
                                                class="inline-block h-4 w-4 rounded border border-gray-300"
                                                style="background-color: {{ $tag->color ?? '#9ca3af' }}"
                                            ></span>
                                            <code class="text-xs">{{ $tag->color }}</code>
                                        </span>
                                    </td>
                                    <td><p>{{ $tag->examples_count }}</p></td>
                                    <td>
                                        <div class="text-center">
                                            @canAccess('example.tag.update')
                                            <button
                                                class="btn-icon warning has-tooltip group"
                                                type="button"
                                                wire:click="startEdit({{ $tag->id }})"
                                            >
                                                <span class="ph ph-pencil-simple"></span>
                                                <span class="tooltip">{{ __('example::lists.rename') }}</span>
                                            </button>
                                            @endcanAccess
                                            @canAccess('example.tag.delete')
                                            <button
                                                class="btn-icon danger has-tooltip group"
                                                type="button"
                                                data-title="{{ __('example::lists.delete_tag_title') }}"
                                                data-message="{{ __('example::lists.delete_tag_message', ['name' => $tag->name]) }}"
                                                data-confirm="{{ __('example::labels.yes_delete') }}"
                                                data-cancel="{{ __('example::labels.cancel') }}"
                                                data-token="{{ $tag->delete_token }}"
                                                data-component="{{ $this->getId() }}"
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
                                            >
                                                <span class="ph ph-trash"></span>
                                                <span class="tooltip">{{ __('example::labels.delete') }}</span>
                                            </button>
                                            @endcanAccess
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        @canAccess('example.tag.create')
                        <tr class="syn-row-muted">
                            <td>
                                <input
                                    class="form-input"
                                    type="text"
                                    maxlength="50"
                                    placeholder="{{ __('example::lists.add_tag') }}"
                                    aria-label="{{ __('example::lists.tag_name') }}"
                                    wire:model="newName"
                                    wire:keydown.enter="addTag"
                                >
                                @error('newName')<p class="form-error">{{ $message }}</p>@enderror
                            </td>
                            <td>
                                <x-synapse-color-picker
                                    wire:model="newColor"
                                    :swatches="$this->swatches"
                                />
                                @error('newColor')<p class="form-error">{{ $message }}</p>@enderror
                            </td>
                            <td></td>
                            <td>
                                <div class="text-center">
                                    <button
                                        class="btn primary"
                                        type="button"
                                        wire:click="addTag"
                                    ><span class="ph ph-plus"></span> {{ __('example::lists.add_tag') }}</button>
                                </div>
                            </td>
                        </tr>
                        @endcanAccess
                    </tbody>
                </table>
            </div>
        </div>
    </x-synapse-panel>
</div>
