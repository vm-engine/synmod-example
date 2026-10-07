<div
    class="space-y-1"
    id="field-form-tags"
>
    <span class="form-label">{{ __('example::forms.tags') }}</span>
    <x-synapse-adv-select
        wire-model="form.tags"
        :multiple="true"
        :allow-create="true"
        search-method="searchTags"
        load-method="loadTags"
        :placeholder="__('example::forms.tags_placeholder')"
    />
    @error('form.tags')<p class="form-error">{{ $message }}</p>@enderror
    @error('form.tags.*')<p class="form-error">{{ $message }}</p>@enderror
</div>
