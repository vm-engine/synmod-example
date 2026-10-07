@if ($errors->any())
    <div
        class="flex flex-col gap-2"
        x-data="{ open: false }"
    >
        <button
            class="btn danger"
            type="button"
            :aria-expanded="open ? 'true' : 'false'"
            @click="open = !open"
        >
            <span class="ph ph-warning-circle"></span>
            {{ trans_choice('example::forms.errors_count', $errors->count(), ['count' => $errors->count()]) }}
            <span
                class="text-xs"
                x-text="open ? '{{ __('example::forms.hide_errors') }}' : '{{ __('example::forms.show_errors') }}'"
            ></span>
        </button>
        <ul
            class="max-h-48 space-y-1 overflow-y-auto text-sm"
            x-cloak
            x-show="open"
        >
            @foreach ($errors->getMessages() as $key => $messages)
                <li>
                    <a
                        class="text-red-600 hover:underline dark:text-red-400"
                        href="#field-{{ str_replace('.', '-', $key) }}"
                    >{{ $messages[0] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
