<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="form-label">{{ __('example::forms.meta') }}</span>
        {{-- The inline toggler renders no label of its own, so show the text beside it. --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('example::forms.raw_json') }}</span>
            <x-synapse-toggler
                :title="__('example::forms.raw_json')"
                :inline="true"
                :checked="$rawJson"
                wire:click="toggleRawJson"
            />
        </div>
    </div>

    @if ($rawJson)
        <textarea
            class="form-input font-mono text-sm"
            id="field-form-metaJson"
            rows="8"
            spellcheck="false"
            wire:model="form.metaJson"
        ></textarea>
        @error('form.metaJson')
            <p class="form-error">{{ $message }}</p>
        @enderror
    @else
        <ul
            class="space-y-2"
            wire:sort="moveMetaRow"
        >
            @foreach ($rows as $index => $row)
                <li
                    class="flex items-start gap-2"
                    wire:key="meta-row-{{ $index }}-{{ md5($row['key']) }}"
                    wire:sort:item="{{ $index }}"
                >
                    <span
                        class="mt-2 cursor-grab text-gray-400"
                        wire:sort:handle
                        aria-hidden="true"
                    ><i class="ph ph-dots-six-vertical"></i></span>
                    <div
                        class="flex-1"
                        id="field-form-meta-{{ $index }}-key"
                    >
                        <input
                            class="form-input"
                            type="text"
                            maxlength="50"
                            placeholder="{{ __('example::forms.meta_key') }}"
                            aria-label="{{ __('example::forms.meta_key') }}"
                            wire:model.blur="form.meta.{{ $index }}.key"
                        >
                        @error('form.meta.' . $index . '.key')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex-1">
                        <input
                            class="form-input"
                            type="text"
                            maxlength="500"
                            placeholder="{{ __('example::forms.meta_value') }}"
                            aria-label="{{ __('example::forms.meta_value') }}"
                            wire:model.blur="form.meta.{{ $index }}.value"
                        >
                        @error('form.meta.' . $index . '.value')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button
                        class="btn-icon danger"
                        type="button"
                        aria-label="{{ __('example::labels.delete') }}"
                        wire:click="removeMetaRow({{ $index }})"
                        wire:sort:ignore
                    ><span class="ph ph-trash"></span></button>
                </li>
            @endforeach
        </ul>
        <button
            class="btn secondary"
            type="button"
            wire:click="addMetaRow"
        ><span class="ph ph-plus"></span> {{ __('example::forms.add_meta') }}</button>
    @endif
</div>
