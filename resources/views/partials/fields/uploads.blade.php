<div class="space-y-4">
    <div
        class="space-y-2"
        id="field-cover"
    >
        <span class="form-label">{{ __('example::forms.cover') }}</span>
        @if ($example?->file)
            <p class="text-xs text-gray-500">{{ __('example::forms.current_cover') }}: <code>{{ basename($example->file) }}</code></p>
        @endif
        <x-synapse-drag-n-drop
            name="cover"
            wire-model="cover"
            accept="image/png,image/jpeg,image/webp"
            :max-size="2048"
            :error="$errors->first('cover')"
        />
    </div>

    <div
        class="space-y-2"
        id="field-attachments"
    >
        <span class="form-label">{{ __('example::forms.attachments') }}</span>
        <x-synapse-file-drop
            wire-model="attachments"
            :multiple="true"
            :max-count="5"
            :max-size-kb="5120"
            :allowed-types="['jpg', 'jpeg', 'png', 'webp', 'pdf']"
            :current-count="$attachmentCount"
            :hint="__('example::forms.attachments_hint', ['max' => $maxAttachments])"
            :error="$errors->first('attachments') ?: $errors->first('attachments.*')"
        />

        @if ($example && $example->attachments->isNotEmpty())
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @foreach ($example->attachments as $attachment)
                    <li
                        class="flex items-center justify-between gap-2 py-2"
                        wire:key="attachment-{{ $attachment->id }}"
                    >
                        <a
                            class="truncate hover:underline"
                            href="{{ $attachment->url() }}"
                            target="_blank"
                            rel="noopener"
                        >
                            <i @class(['ph', 'ph-image' => $attachment->isImage(), 'ph-file-pdf' => !$attachment->isImage()])></i>
                            {{ $attachment->original_name }}
                        </a>
                        <button
                            class="btn-icon danger"
                            type="button"
                            aria-label="{{ __('example::labels.delete') }}"
                            wire:click="deleteAttachment('{{ $attachment->delete_token }}')"
                        ><span class="ph ph-trash"></span></button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
