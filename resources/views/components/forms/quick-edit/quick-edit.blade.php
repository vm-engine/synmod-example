<div>
    <form wire:submit="save">
        <div class="syn-modal-header">
            <h3 class="syn-modal-title">{{ __('example::forms.quick_edit') }}</h3>
        </div>
        <div class="syn-modal-body space-y-4">
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="quick-text"
                >{{ __('example::forms.text') }}</label>
                <input
                    class="form-input"
                    id="quick-text"
                    type="text"
                    wire:model="text"
                >
                @error('text')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="quick-status"
                >{{ __('example::forms.status') }}</label>
                <select
                    class="form-input"
                    id="quick-status"
                    wire:model="status"
                >
                    @foreach ($this->statusOptions() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="quick-due"
                >{{ __('example::forms.due_at') }}</label>
                <x-synapse-datepicker
                    id="quick-due"
                    wire:model="due_at"
                />
                @error('due_at')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="syn-modal-footer flex justify-end gap-2">
            <button
                class="btn secondary"
                type="button"
                @click="$dispatch('close-modal-quick-edit')"
            >{{ __('example::forms.cancel') }}</button>
            <button
                class="btn primary"
                type="submit"
            >{{ __('example::forms.save') }}</button>
        </div>
    </form>
</div>
