<div x-data="{ pageName: 'Conditional permission', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::integrations.abac')">
        <div class="syn-panel-body space-y-3 px-5 py-4 text-sm sm:px-6">
            <p>{{ __('example::integrations.abac_intro') }}</p>
            <div class="flex items-center gap-2">
                <code class="min-w-0 break-all rounded bg-gray-100 px-2 py-1 font-mono text-xs dark:bg-gray-800">{{ $this->conditionJson }}</code>
                <x-synapse-copy-button
                    :text="$this->conditionJson"
                    :icon-only="true"
                />
            </div>
            <x-synapse-alert
                type="info"
                :message="__('example::integrations.abac_fail_closed')"
            />
        </div>

        <div class="syn-panel-body max-w-full overflow-x-auto">
            <table class="datatable min-w-full">
                <thead>
                    <tr>
                        <th><p>Text</p></th>
                        <th><p>{{ __('example::integrations.creator') }}</p></th>
                        <th><p>{{ __('example::integrations.verdict') }}</p></th>
                        <th class="datatable-col-actions"><p>{{ __('example::integrations.rename') }}</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->rows as $row)
                        <tr wire:key="abac-{{ $row['example']->id }}">
                            <td>
                                @if ($renamingId === $row['example']->id)
                                    <form
                                        class="flex items-center gap-2"
                                        wire:submit="rename"
                                    >
                                        <input
                                            class="form-input"
                                            type="text"
                                            maxlength="255"
                                            aria-label="{{ __('example::integrations.rename') }}"
                                            wire:model="renameText"
                                        >
                                        <button
                                            class="btn primary"
                                            type="submit"
                                        >{{ __('example::pages.save') }}</button>
                                        <button
                                            class="btn secondary"
                                            type="button"
                                            wire:click="cancelRename"
                                        >{{ __('example::pages.cancel') }}</button>
                                    </form>
                                    @error('renameText')<p class="form-error">{{ $message }}</p>@enderror
                                @else
                                    <p>{{ $row['example']->text }}</p>
                                @endif
                            </td>
                            <td><p>{{ $row['example']->creator?->name ?? '—' }}</p></td>
                            <td>
                                <x-synapse-badge
                                    :color="$row['allowed'] ? 'success' : 'danger'"
                                    size="sm"
                                >{{ $row['allowed'] ? __('example::integrations.allowed') : __('example::integrations.denied') }}</x-synapse-badge>
                                <span class="ml-1 font-mono text-xs text-gray-500">{{ $row['source'] }}</span>
                            </td>
                            <td>
                                <button
                                    class="btn-icon has-tooltip group"
                                    type="button"
                                    wire:click="startRename({{ $row['example']->id }})"
                                >
                                    <span class="ph ph-pencil-simple"></span>
                                    <span class="tooltip">{{ __('example::integrations.rename') }}</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-synapse-panel>
</div>
