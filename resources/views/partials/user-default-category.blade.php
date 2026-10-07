<div class="form-box">
    <label for="example-default-category">{{ __('example::integrations.default_category') }}</label>
    <select
        class="form-input"
        id="example-default-category"
        wire:model="extensionData.example_default_category_id"
    >
        <option value="">{{ __('example::integrations.no_default_category') }}</option>
        @foreach ($categories as $category)
            <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
        @endforeach
    </select>
    <p class="form-help">{{ __('example::integrations.default_category_hint') }}</p>
    @error('extensionData.example_default_category_id')<p class="form-error">{{ $message }}</p>@enderror
</div>
