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
    <input
        class="form-input"
        type="date"
        aria-label="{{ __('example::lists.due_from') }}"
        title="{{ __('example::lists.due_from') }}"
        wire:model.live="dueFrom"
    >
    <div class="flex gap-2">
        <input
            class="form-input flex-1"
            type="date"
            aria-label="{{ __('example::lists.due_to') }}"
            title="{{ __('example::lists.due_to') }}"
            wire:model.live="dueTo"
        >
        <button
            class="btn secondary"
            type="button"
            wire:click="resetFilters"
        >{{ __('example::lists.reset_filters') }}</button>
    </div>
</div>
