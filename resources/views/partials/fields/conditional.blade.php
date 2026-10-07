<div class="space-y-4">
    {{-- Server-side condition: a required publish note only when the status is Published. --}}
    @if ($status === 'published')
        <div
            class="space-y-1"
            id="field-form-publish_note"
        >
            <label
                class="form-label"
                for="publish-note"
            >{{ __('example::forms.publish_note') }}</label>
            <textarea
                class="form-input"
                id="publish-note"
                rows="2"
                maxlength="500"
                wire:model.blur="form.publish_note"
            ></textarea>
            <p class="form-help">{{ __('example::forms.publish_note_help') }}</p>
            @error('form.publish_note')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    @endif

    {{-- Client-side condition: the due date appears as soon as Schedule is ticked. --}}
    <div
        class="space-y-2"
        x-data="{ schedule: {{ $schedule ? 'true' : 'false' }} }"
    >
        <label class="flex items-center gap-2">
            <input
                class="form-checkbox"
                type="checkbox"
                x-model="schedule"
                wire:model="form.schedule"
            >
            <span>{{ __('example::forms.schedule') }}</span>
        </label>
        <div
            id="field-form-due_at"
            x-cloak
            x-show="schedule"
        >
            <x-synapse-datepicker
                wire:model="form.due_at"
                aria-label="{{ __('example::forms.due_at') }}"
            />
            @error('form.due_at')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
