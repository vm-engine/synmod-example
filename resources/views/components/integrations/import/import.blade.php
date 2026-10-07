<div x-data="{ pageName: 'Excel import', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="space-y-5">
        <x-synapse-panel :title="__('example::integrations.import')">
            <x-slot:toolbar>
                <button
                    class="btn secondary"
                    type="button"
                    wire:click="downloadTemplate"
                ><span class="ph ph-file-xls"></span> {{ __('example::integrations.import_template') }}</button>
            </x-slot:toolbar>
            <form
                class="syn-panel-body space-y-4 px-5 py-4 text-sm sm:px-6"
                wire:submit="startImport"
            >
                <p>{{ __('example::integrations.import_intro') }}</p>
                <x-synapse-file-drop
                    wire-model="upload"
                    :multiple="false"
                    :max-size-kb="5120"
                    :allowed-types="['xlsx', 'csv']"
                    :hint="__('example::integrations.import_file')"
                    :error="$errors->first('upload')"
                />
                <button
                    class="btn primary"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="upload,startImport"
                ><span class="ph ph-upload-simple"></span> {{ __('example::integrations.import_start') }}</button>
            </form>
        </x-synapse-panel>

        <div @if ($this->running) wire:poll.2s @endif>
            @if ($this->task)
                <x-synapse-panel :title="__('example::integrations.import_progress')">
                    <div class="syn-panel-body space-y-3 px-5 py-4 text-sm sm:px-6">
                        @if ($this->isCompleted)
                            <x-synapse-alert
                                type="success"
                                :message="__('example::integrations.import_done', ['imported' => $this->report['imported'], 'skipped' => $this->report['skipped']])"
                            />
                        @elseif ($this->running)
                            <p>{{ $this->isPending ? __('example::integrations.import_waiting') : __('example::integrations.import_running', ['processed' => $this->task->processed_rows, 'total' => $this->task->total_rows ?? '?']) }}</p>
                        @else
                            <x-synapse-alert
                                type="danger"
                                :message="__('example::integrations.import_failed', ['error' => $this->task->error_message ?? '—'])"
                            />
                        @endif
                    </div>

                    @if ($this->report['errors'] !== [])
                        <div class="syn-panel-body max-w-full overflow-x-auto">
                            <table class="datatable min-w-full">
                                <thead>
                                    <tr>
                                        <th><p>{{ __('example::integrations.import_row') }}</p></th>
                                        <th><p>{{ __('example::integrations.import_reasons') }}</p></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->report['errors'] as $error)
                                        <tr wire:key="import-error-{{ $error['row'] }}">
                                            <td><p>{{ $error['row'] }}</p></td>
                                            <td><p>{{ implode(' ', $error['messages']) }}</p></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($this->report['skipped'] > count($this->report['errors']))
                            <p class="px-5 py-3 text-xs text-gray-500 sm:px-6">{{ __('example::integrations.import_more_skipped', ['count' => count($this->report['errors'])]) }}</p>
                        @endif
                    @endif
                </x-synapse-panel>
            @endif
        </div>
    </div>
</div>
