<div class="detail-attachments">
    @if ($this->attachments->isEmpty())
        <p class="py-6 text-center text-sm text-gray-500">{{ __('example::pages.no_attachments') }}</p>
    @else
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($this->attachments as $attachment)
                <li
                    class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800"
                    wire:key="detail-attachment-{{ $attachment->id }}"
                >
                    @if ($attachment->isImage())
                        <button
                            class="block w-full"
                            type="button"
                            data-url="{{ $attachment->url() }}"
                            @click="$dispatch('open-lightbox-example-attachments', { url: $el.dataset.url })"
                        >
                            <img
                                class="h-28 w-full object-cover"
                                src="{{ $attachment->url() }}"
                                alt="{{ $attachment->original_name }}"
                                loading="lazy"
                            >
                        </button>
                    @else
                        <a
                            class="flex h-28 flex-col items-center justify-center gap-1 text-sm"
                            href="{{ $attachment->url() }}"
                            target="_blank"
                            rel="noopener"
                        ><i class="ph ph-file-pdf text-3xl text-red-500"></i></a>
                    @endif
                    <div class="flex items-center justify-between gap-2 px-2 py-1 text-xs">
                        <span class="truncate">{{ $attachment->original_name }}</span>
                        <button
                            class="btn-icon danger"
                            type="button"
                            aria-label="{{ __('example::pages.delete_attachment') }}"
                            data-title="{{ __('example::pages.delete_attachment') }}"
                            data-message="{{ __('example::pages.delete_attachment_confirm') }}"
                            data-confirm="{{ __('example::pages.yes_delete') }}"
                            data-cancel="{{ __('example::pages.cancel') }}"
                            data-token="{{ $attachment->delete_token }}"
                            data-component="{{ $this->getId() }}"
                            @click="$dispatch('confirm-dialog', {
                                title: $el.dataset.title,
                                message: $el.dataset.message,
                                confirmText: $el.dataset.confirm,
                                cancelText: $el.dataset.cancel,
                                confirmColor: 'danger',
                                icon: 'ph ph-trash',
                                wireMethod: 'deleteAttachment',
                                wireParams: [$el.dataset.token],
                                wireComponent: $el.dataset.component
                            })"
                        ><span class="ph ph-trash"></span></button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
