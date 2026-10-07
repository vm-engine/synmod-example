<div class="mx-auto max-w-3xl px-4 py-10">
    <a
        class="text-sm text-gray-500 hover:underline"
        href="{{ route('example.index') }}"
    >← {{ __('example::frontend.back') }}</a>

    <article class="mt-4">
        <p class="text-sm text-gray-500">{{ $this->example->category?->name }}</p>
        <h1 class="mt-1 text-3xl font-semibold">{{ $this->example->text }}</h1>

        @if ($this->coverUrl)
            <img
                class="mt-6 w-full rounded-xl object-cover"
                src="{{ $this->coverUrl }}"
                alt="{{ $this->example->text }}"
            >
        @endif

        @if ($this->example->content)
            {{-- Purified on save (RichTextSanitizer). --}}
            <div class="prose dark:prose-invert mt-6 max-w-none">{!! $this->example->content !!}</div>
        @endif

        @if ($this->example->tags->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($this->example->tags as $tag)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs dark:bg-gray-800">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($this->images !== [])
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($this->images as $image)
                    <img
                        class="h-40 w-full rounded-lg object-cover"
                        src="{{ $image['url'] }}"
                        alt="{{ $image['name'] }}"
                        loading="lazy"
                        wire:key="fe-image-{{ $loop->index }}"
                    >
                @endforeach
            </div>
        @endif
    </article>
</div>
