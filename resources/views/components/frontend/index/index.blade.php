<div class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <h1 class="text-3xl font-semibold">{{ __('example::frontend.title') }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <select
                class="form-input"
                aria-label="{{ __('example::frontend.category') }}"
                wire:model.live="category"
            >
                <option value="">{{ __('example::frontend.all_categories') }}</option>
                @foreach ($this->categories as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
            <a
                class="btn secondary"
                href="{{ route('example.search') }}"
            ><span class="ph ph-magnifying-glass"></span> {{ __('example::frontend.search') }}</a>
        </div>
    </div>

    @if ($this->examples->isEmpty())
        <p class="py-16 text-center text-gray-500">{{ __('example::frontend.none') }}</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->examples as $example)
                <article
                    class="rounded-xl border border-gray-200 p-5 dark:border-gray-800"
                    wire:key="fe-example-{{ $example->id }}"
                >
                    <p class="text-xs text-gray-500">{{ $example->category?->name }}</p>
                    <h2 class="mt-1 text-lg font-semibold">
                        <a
                            class="hover:underline"
                            href="{{ route('example.show', $example->slug) }}"
                        >{{ $example->text }}</a>
                    </h2>
                    @if ($example->due_at)
                        <p class="mt-2 text-sm text-gray-500">{{ __('example::frontend.due', ['date' => $example->due_at->toDateString()]) }}</p>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $this->examples->links() }}</div>
    @endif
</div>
