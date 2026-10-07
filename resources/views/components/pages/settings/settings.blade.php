<div x-data="{ pageName: 'Settings', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::pages.settings')">
        <div class="syn-panel-body px-5 pt-3 sm:px-6">
            <nav
                class="syn-tabs"
                role="tablist"
            >
                @foreach (['lists' => __('example::pages.settings_lists'), 'editor' => __('example::pages.settings_editor'), 'board' => __('example::pages.settings_board')] as $tab => $label)
                    <button
                        type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                        wire:key="settings-tab-{{ $tab }}"
                        wire:click="selectTab('{{ $tab }}')"
                        @class(['syn-tab', 'syn-tab-active' => $activeTab === $tab])
                    >{{ $label }}</button>
                @endforeach
            </nav>
        </div>

        <div class="px-5 py-4 sm:px-6">
            @if ($activeTab === 'lists')
                <form
                    class="max-w-md space-y-4"
                    wire:submit="saveLists"
                >
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="settings-per-page"
                        >{{ __('example::pages.per_page') }}</label>
                        <select
                            class="form-input"
                            id="settings-per-page"
                            wire:model="perPage"
                        >
                            @foreach ($this->perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('perPage')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2">
                        <input
                            class="form-checkbox"
                            type="checkbox"
                            wire:model="showDueColumn"
                        >
                        <span>{{ __('example::pages.show_due_column') }}</span>
                    </label>
                    <button
                        class="btn primary"
                        type="submit"
                    >{{ __('example::pages.save') }}</button>
                </form>
            @elseif ($activeTab === 'editor')
                <form
                    class="max-w-md space-y-4"
                    wire:submit="saveEditor"
                >
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="settings-status"
                        >{{ __('example::pages.default_status') }}</label>
                        <select
                            class="form-input"
                            id="settings-status"
                            wire:model="defaultStatus"
                        >
                            @foreach ($this->statuses as $status)
                                <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                            @endforeach
                        </select>
                        @error('defaultStatus')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="settings-max"
                        >{{ __('example::pages.max_attachments') }}</label>
                        <input
                            class="form-input"
                            id="settings-max"
                            type="number"
                            min="1"
                            max="50"
                            wire:model="maxAttachments"
                        >
                        @error('maxAttachments')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <button
                        class="btn primary"
                        type="submit"
                    >{{ __('example::pages.save') }}</button>
                </form>
            @else
                <form
                    class="max-w-md space-y-4"
                    wire:submit="saveBoard"
                >
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="settings-wip"
                        >{{ __('example::pages.wip_limit') }}</label>
                        <input
                            class="form-input"
                            id="settings-wip"
                            type="number"
                            min="0"
                            max="99"
                            wire:model="wipLimit"
                        >
                        @error('wipLimit')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <button
                        class="btn primary"
                        type="submit"
                    >{{ __('example::pages.save') }}</button>
                </form>
            @endif
        </div>
    </x-synapse-panel>
</div>
