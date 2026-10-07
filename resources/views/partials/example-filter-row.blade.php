<div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
    <div class="xl:col-span-2">
        <x-synapse-search-box
            :placeholder="__('example::labels.search_placeholder')"
            wire:model.live.debounce="q"
        />
    </div>
    <select
        class="form-input"
        wire:model.live="filterStatus"
        aria-label="{{ __('example::lists.filter_status') }}"
    >
        <option value="">{{ __('example::lists.filter_status') }}: {{ __('example::lists.all') }}</option>
        @foreach ($statuses as $status)
            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
        @endforeach
    </select>
    <select
        class="form-input"
        wire:model.live="filterCategory"
        aria-label="{{ __('example::lists.filter_category') }}"
    >
        <option value="">{{ __('example::lists.filter_category') }}: {{ __('example::lists.all') }}</option>
        @foreach ($categories as $category)
            <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
        @endforeach
    </select>
    <div title="{{ __('example::lists.due_from') }}">
        <x-synapse-datepicker
            wire:model.live="dueFrom"
            :placeholder="__('example::lists.due_from')"
            aria-label="{{ __('example::lists.due_from') }}"
        />
    </div>
    <div class="flex gap-2">
        <div
            class="flex-1"
            title="{{ __('example::lists.due_to') }}"
        >
            <x-synapse-datepicker
                wire:model.live="dueTo"
                :placeholder="__('example::lists.due_to')"
                aria-label="{{ __('example::lists.due_to') }}"
            />
        </div>
        <button
            class="btn secondary"
            type="button"
            wire:click="resetFilters"
        >{{ __('example::lists.reset_filters') }}</button>
    </div>
</div>
