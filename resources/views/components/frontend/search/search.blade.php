<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-3xl font-semibold">{{ __('example::frontend.search') }}</h1>

    <form
        class="mt-6 flex gap-2"
        action="{{ route('example.search') }}"
        method="get"
    >
        <input
            class="form-input flex-1"
            name="q"
            type="search"
            maxlength="100"
            value="{{ $q }}"
            placeholder="{{ __('example::frontend.search_placeholder') }}"
            aria-label="{{ __('example::frontend.search') }}"
            wire:model.live.debounce.400ms="q"
        >
        <button
            class="btn primary"
            type="submit"
        >{{ __('example::frontend.search_button') }}</button>
    </form>

    @if ($this->results !== null)
        @if ($this->results->isEmpty())
            <p class="mt-8 text-gray-500">{{ __('example::frontend.no_results', ['q' => $this->term]) }}</p>
        @else
            <p class="mt-6 text-sm text-gray-500">{{ __('example::frontend.results', ['count' => $this->results->total(), 'q' => $this->term]) }}</p>
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($this->results as $example)
                    <li
                        class="py-4"
                        wire:key="fe-result-{{ $example->id }}"
                    >
                        <a
                            class="text-lg font-medium hover:underline"
                            href="{{ route('example.show', $example->slug) }}"
                        >{{ $this->highlight($example->text) }}</a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6">{{ $this->results->links() }}</div>
        @endif
    @endif
</div>
