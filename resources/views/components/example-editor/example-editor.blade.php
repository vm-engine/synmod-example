<div x-data="{ pageName: 'Example editor', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <form
        class="pb-24"
        wire:submit="save"
    >
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-5">
                <x-synapse-panel :title="__('example::forms.tab_general')">
                    <div class="syn-panel-body space-y-4 px-5 py-4 sm:px-6">
                        <div
                            class="space-y-1"
                            id="field-form-text"
                        >
                            <label
                                class="form-label"
                                for="editor-text"
                            >{{ __('example::forms.text') }}</label>
                            <input
                                class="form-input"
                                id="editor-text"
                                type="text"
                                maxlength="255"
                                wire:model.live.blur="form.text"
                            >
                            @error('form.text')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        @include('example::partials.fields.slug')

                        <div
                            class="space-y-1"
                            id="field-form-email"
                        >
                            <label
                                class="form-label"
                                for="editor-email"
                            >{{ __('example::forms.email') }}</label>
                            <input
                                class="form-input"
                                id="editor-email"
                                type="email"
                                wire:model.live.blur="form.email"
                            >
                            @error('form.email')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </x-synapse-panel>

                <x-synapse-panel :title="__('example::forms.content')">
                    <div
                        class="syn-panel-body px-5 py-4 sm:px-6"
                        id="field-form-content"
                    >
                        <x-example::rich-text
                            field="form.content"
                            :value="$this->form->content"
                        />
                        @error('form.content')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </x-synapse-panel>

                <x-synapse-panel :title="__('example::forms.meta')">
                    <div class="syn-panel-body px-5 py-4 sm:px-6">
                        @include('example::partials.fields.repeater', ['rows' => $this->form->meta, 'rawJson' => $this->form->rawJson])
                    </div>
                </x-synapse-panel>

                <x-synapse-panel :title="__('example::forms.attachments')">
                    <div class="syn-panel-body px-5 py-4 sm:px-6">
                        @include('example::partials.fields.uploads', ['example' => $this->form->example, 'attachmentCount' => $this->attachmentCount, 'maxAttachments' => $this->maxAttachments()])
                    </div>
                </x-synapse-panel>
            </div>

            <div class="space-y-5">
                <x-synapse-panel :title="__('example::forms.status')">
                    <div class="syn-panel-body space-y-4 px-5 py-4 sm:px-6">
                        <div id="field-form-status">
                            <select
                                class="form-input"
                                aria-label="{{ __('example::forms.status') }}"
                                wire:model.live="form.status"
                            >
                                @foreach ($this->statuses as $status)
                                    <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                @endforeach
                            </select>
                            @error('form.status')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        @include('example::partials.fields.conditional', ['status' => $this->form->status, 'schedule' => $this->form->schedule])
                    </div>
                </x-synapse-panel>

                <x-synapse-panel :title="__('example::forms.category')">
                    <div
                        class="syn-panel-body space-y-4 px-5 py-4 sm:px-6"
                        id="field-form-category_id"
                    >
                        <x-synapse-adv-select
                            wire-model="form.category_id"
                            :options="$this->categories"
                            :placeholder="__('example::forms.no_category')"
                        />
                        @error('form.category_id')<p class="form-error">{{ $message }}</p>@enderror

                        @include('example::partials.fields.tags')
                    </div>
                </x-synapse-panel>

                <x-synapse-panel :title="__('example::forms.color')">
                    <div
                        class="syn-panel-body px-5 py-4 sm:px-6"
                        id="field-form-color"
                    >
                        <x-synapse-color-picker
                            wire:model="form.color"
                            :swatches="$this->swatches"
                        />
                        @error('form.color')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </x-synapse-panel>
            </div>
        </div>

        {{-- Sticky action bar with the error summary. --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur lg:left-[290px] dark:border-gray-800 dark:bg-gray-900/95">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    @include('example::partials.error-summary')
                </div>
                <div class="flex gap-2">
                    <a
                        class="btn secondary"
                        href="{{ backend_route('example.index') }}"
                        wire:navigate
                    >{{ __('example::forms.cancel') }}</a>
                    <button
                        class="btn primary"
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save,cover,attachments"
                    ><span class="ph ph-floppy-disk"></span> {{ __('example::forms.save') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
