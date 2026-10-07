<div x-data="{ pageName: 'Modal wizard', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::forms.modal_wizard')">
        <x-slot:toolbar>
            <button
                class="btn primary"
                type="button"
                @click="$dispatch('open-modal-example-wizard')"
            ><span class="ph ph-magic-wand"></span> {{ __('example::forms.start_wizard') }}</button>
        </x-slot:toolbar>
        <div class="syn-panel-body px-5 py-4 sm:px-6">
            <p class="syn-section-label">{{ __('example::forms.recently_created') }}</p>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($this->recent as $example)
                    <li wire:key="recent-{{ $example->id }}">{{ $example->text }} <span class="text-gray-500">· {{ $example->status->label() }}</span></li>
                @endforeach
            </ul>
        </div>
    </x-synapse-panel>

    <x-synapse-modal
        name="example-wizard"
        maxWidth="2xl"
    >
        <div class="syn-modal-header">
            <h3 class="syn-modal-title">{{ __('example::forms.modal_wizard') }}</h3>
        </div>
        <div class="syn-modal-body space-y-5">
            <x-synapse-steps
                :steps="$this->stepLabels"
                :current="$step"
                go-method="goToStep"
            />

            @if ($step === 1)
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="mw-text"
                        >{{ __('example::forms.text') }}</label>
                        <input
                            class="form-input"
                            id="mw-text"
                            type="text"
                            wire:model="text"
                        >
                        @error('text')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="mw-email"
                        >{{ __('example::forms.email') }}</label>
                        <input
                            class="form-input"
                            id="mw-email"
                            type="email"
                            wire:model="email"
                        >
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @elseif ($step === 2)
                <div class="space-y-4">
                    <select
                        class="form-input"
                        aria-label="{{ __('example::forms.status') }}"
                        wire:model="status"
                    >
                        @foreach ($this->statuses as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="space-y-1">
                        <label
                            class="form-label"
                            for="mw-due"
                        >{{ __('example::forms.due_at') }}</label>
                        <x-synapse-datepicker
                            id="mw-due"
                            wire:model="due_at"
                        />
                        @error('due_at')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @else
                <dl class="grid grid-cols-3 gap-2 text-sm">
                    <dt class="text-gray-500">{{ __('example::forms.text') }}</dt><dd class="col-span-2">{{ $text }}</dd>
                    <dt class="text-gray-500">{{ __('example::forms.email') }}</dt><dd class="col-span-2">{{ $email }}</dd>
                    <dt class="text-gray-500">{{ __('example::forms.category') }}</dt><dd class="col-span-2">{{ $this->categoryName ?? '—' }}</dd>
                    <dt class="text-gray-500">{{ __('example::forms.status') }}</dt><dd class="col-span-2">{{ $this->statusLabel }}</dd>
                    <dt class="text-gray-500">{{ __('example::forms.due_at') }}</dt><dd class="col-span-2">{{ $due_at ?: '—' }}</dd>
                </dl>
            @endif
        </div>
        <div class="syn-modal-footer flex justify-between gap-2">
            <button
                class="btn secondary"
                type="button"
                wire:click="back"
                @disabled($step === 1)
            >{{ __('example::forms.back') }}</button>
            @if ($step < 3)
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
    </x-synapse-modal>
</div>
