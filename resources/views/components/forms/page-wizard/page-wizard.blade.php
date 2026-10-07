<div x-data="{ pageName: 'Page wizard', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::forms.page_wizard')">
        <div class="syn-panel-body space-y-6 px-5 py-5 sm:px-6">
            <x-synapse-steps
                :steps="$this->stepLabels"
                :current="$step"
                go-method="goToStep"
            />

            <div @class(['space-y-4', 'hidden' => $step !== 1])>
                <div class="space-y-1">
                    <label
                        class="form-label"
                        for="pw-text"
                    >{{ __('example::forms.text') }}</label>
                    <input
                        class="form-input"
                        id="pw-text"
                        type="text"
                        wire:model="text"
                    >
                    @error('text')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1">
                    <label
                        class="form-label"
                        for="pw-email"
                    >{{ __('example::forms.email') }}</label>
                    <input
                        class="form-input"
                        id="pw-email"
                        type="email"
                        wire:model="email"
                    >
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1">
                    <span class="form-label">{{ __('example::forms.category') }}</span>
                    <x-synapse-adv-select
                        wire-model="category_id"
                        :options="$this->categories"
                        :placeholder="__('example::forms.no_category')"
                    />
                </div>
            </div>

            <div @class(['space-y-4', 'hidden' => $step !== 2])>
                <x-example::rich-text
                    field="content"
                    :value="$content"
                />
                <x-synapse-color-picker wire:model="color" />
                @error('color')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div @class(['space-y-1', 'hidden' => $step !== 3])>
                <span class="form-label">{{ __('example::forms.tags') }}</span>
                <x-synapse-adv-select
                    wire-model="tags"
                    :multiple="true"
                    :allow-create="true"
                    search-method="searchTags"
                    load-method="loadTags"
                    :placeholder="__('example::forms.tags_placeholder')"
                />
                @error('tags')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            @if ($step === 4)
                <div class="space-y-3">
                    <p class="text-sm text-gray-500">{{ __('example::forms.review_intro') }}</p>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="text-gray-500">{{ __('example::forms.text') }}</dt><dd class="col-span-2">{{ $text }}</dd>
                        <dt class="text-gray-500">{{ __('example::forms.email') }}</dt><dd class="col-span-2">{{ $email }}</dd>
                        <dt class="text-gray-500">{{ __('example::forms.category') }}</dt><dd class="col-span-2">{{ $this->review['category'] ?? '—' }}</dd>
                        <dt class="text-gray-500">{{ __('example::forms.color') }}</dt><dd class="col-span-2"><code>{{ $color }}</code></dd>
                        <dt class="text-gray-500">{{ __('example::forms.content') }}</dt><dd class="col-span-2">{{ $this->review['content'] ?: '—' }}</dd>
                        <dt class="text-gray-500">{{ __('example::forms.tags') }}</dt>
                        <dd class="col-span-2 flex flex-wrap gap-1">
                            @foreach ($this->review['tags'] as $name)
                                <x-synapse-badge size="sm">{{ $name }}</x-synapse-badge>
                            @endforeach
                        </dd>
                    </dl>
                </div>
            @endif
        </div>

        <div class="syn-panel-footer flex justify-between gap-2 px-5 py-4 sm:px-6">
            <button
                class="btn secondary"
                type="button"
                wire:click="back"
                @disabled($step === 1)
            >{{ __('example::forms.back') }}</button>
            @if ($step < 4)
                <button
                    class="btn primary"
                    type="button"
                    wire:click="next"
                >{{ __('example::forms.next') }}</button>
            @else
                <button
                    class="btn primary"
                    type="button"
                    wire:click="finish"
                >{{ __('example::forms.finish') }}</button>
            @endif
        </div>
    </x-synapse-panel>
</div>
