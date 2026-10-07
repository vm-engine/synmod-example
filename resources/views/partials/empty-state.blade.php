<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
        <i class="{{ $icon }} text-2xl"></i>
    </span>
    <h4 class="mt-3 font-semibold text-gray-800 dark:text-gray-200">{{ $title }}</h4>
    @if ($text)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $text }}</p>
    @endif
    @if ($action)
        @isset($action['href'])
            <a
                class="btn primary mt-4"
                href="{{ $action['href'] }}"
                wire:navigate
            >{{ $action['label'] }}</a>
        @else
            <button
                class="btn secondary mt-4"
                type="button"
                wire:click="{{ $action['click'] }}"
            >{{ $action['label'] }}</button>
        @endisset
    @endif
</div>
