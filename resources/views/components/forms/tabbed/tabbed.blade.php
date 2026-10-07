<div x-data="{ pageName: 'Tabbed form', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <form wire:submit="save">
        <x-synapse-panel :title="__('example::forms.tabbed')">
            <div class="syn-panel-body px-5 pt-3 sm:px-6">
                <nav
                    class="syn-tabs"
                    role="tablist"
                >
                    @foreach (['general' => __('example::forms.tab_general'), 'content' => __('example::forms.tab_content'), 'meta' => __('example::forms.tab_meta')] as $tab => $label)
                        <button
                            type="button"
                            role="tab"
                            aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                            wire:key="tab-{{ $tab }}"
                            wire:click="selectTab('{{ $tab }}')"
                            @class(['syn-tab', 'syn-tab-active' => $activeTab === $tab])
                        >
                            {{ $label }}
                            @if ($this->tabErrors[$tab] > 0)
                                <x-synapse-badge
                                    color="danger"
                                    size="sm"
                                >{{ $this->tabErrors[$tab] }}</x-synapse-badge>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="space-y-4 px-5 py-4 sm:px-6">
                <div @class(['space-y-4', 'hidden' => $activeTab !== 'general'])>
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="tab-text"
                        >{{ __('example::forms.text') }}</label>
                        <input
                            class="form-input"
                            id="tab-text"
                            type="text"
                            wire:model.live.blur="form.text"
                        >
                        @error('form.text')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    @include('example::partials.fields.slug')
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="tab-email"
                        >{{ __('example::forms.email') }}</label>
                        <input
                            class="form-input"
                            id="tab-email"
                            type="email"
                            wire:model="form.email"
                        >
                        @error('form.email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <select
                        class="form-input"
                        aria-label="{{ __('example::forms.status') }}"
                        wire:model.live="form.status"
                    >
                        @foreach ($this->statuses as $status)
                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div @class(['space-y-4', 'hidden' => $activeTab !== 'content'])>
                    <x-example::rich-text
                        field="form.content"
                        :value="$this->form->content"
                    />
                    <x-synapse-color-picker wire:model="form.color" />
                    @error('form.color')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div @class(['space-y-4', 'hidden' => $activeTab !== 'meta'])>
                    @include('example::partials.fields.repeater', ['rows' => $this->form->meta, 'rawJson' => $this->form->rawJson])
                    @include('example::partials.fields.conditional', ['status' => $this->form->status, 'schedule' => $this->form->schedule])
                </div>
            </div>

            <div class="syn-panel-footer flex justify-end gap-2 px-5 py-4 sm:px-6">
                <button
                    class="btn primary"
                    type="submit"
                ><span class="ph ph-floppy-disk"></span> {{ __('example::forms.save') }}</button>
            </div>
        </x-synapse-panel>
    </form>
</div>
