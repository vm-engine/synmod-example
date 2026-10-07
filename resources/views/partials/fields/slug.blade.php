<div class="space-y-1">
    <label
        class="form-label"
        for="field-form-slug"
    >{{ __('example::forms.slug') }}</label>
    <input
        class="form-input font-mono"
        id="field-form-slug"
        type="text"
        maxlength="255"
        wire:model.live.blur="form.slug"
    >
    <p class="form-help">{{ __('example::forms.slug_help') }}</p>
    @error('form.slug')<p class="form-error">{{ $message }}</p>@enderror
</div>
