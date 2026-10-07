<div x-data="{ pageName: 'Calendar', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::pages.calendar')">
        <div class="syn-panel-body px-5 py-4 sm:px-6">
            <x-synapse-calendar
                :month="$month"
                :events="$this->events"
                nav-method="setMonth"
                event-method="openEvent"
                day-method="createOn"
            />
        </div>
    </x-synapse-panel>

    <x-synapse-modal
        name="calendar-create"
        maxWidth="md"
    >
        <form wire:submit="saveQuick">
            <div class="syn-modal-header">
                <h3 class="syn-modal-title">{{ __('example::pages.new_on', ['date' => $quickDate]) }}</h3>
            </div>
            <div class="syn-modal-body space-y-4">
                <div class="space-y-1">
                    <label
                        class="form-label"
                        for="quick-text"
                    >{{ __('example::pages.text') }}</label>
                    <input
                        class="form-input"
                        id="quick-text"
                        type="text"
                        wire:model="quickText"
                    >
                    @error('quickText')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1">
                    <label
                        class="form-label"
                        for="quick-email"
                    >{{ __('example::pages.email') }}</label>
                    <input
                        class="form-input"
                        id="quick-email"
                        type="email"
                        wire:model="quickEmail"
                    >
                    @error('quickEmail')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <select
                    class="form-input"
                    aria-label="{{ __('example::pages.status') }}"
                    wire:model="quickStatus"
                >
                    @foreach ($this->statuses as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="syn-modal-footer flex justify-end gap-2">
                <button
                    class="btn secondary"
                    type="button"
                    @click="$dispatch('close-modal-calendar-create')"
                >{{ __('example::pages.cancel') }}</button>
                <button
                    class="btn primary"
                    type="submit"
                >{{ __('example::pages.create') }}</button>
            </div>
        </form>
    </x-synapse-modal>
</div>
