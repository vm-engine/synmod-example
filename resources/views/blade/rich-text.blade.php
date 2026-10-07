<div
    wire:ignore
    x-data="exampleRichText({{ json_encode($config()) }})"
>
    <textarea
        class="form-input"
        id="rich-text-{{ str_replace(['.', '[', ']'], '-', $field) }}"
        x-ref="textarea"
        rows="10"
    >{{ $value }}</textarea>
</div>
