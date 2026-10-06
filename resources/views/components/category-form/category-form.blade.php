<div x-data="exampleCategoryForm()">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            @if ($form->category)
                {{ __('example::labels.edit_category') }}
            @else
                {{ __('example::labels.add_category') }}
            @endif
        </h3>
        <button
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            type="button"
            @click="$dispatch('close-modal-category-form')"
        >
            <span class="sr-only">{{ __('example::labels.cancel') }}</span>
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 space-y-4">
        <div class="form-box required">
            <label for="name">{{ __('example::labels.name') }}</label>
            <div class="input-group">
                <input
                    class="form-input @error('form.name') has-error @enderror"
                    id="name"
                    name="name"
                    type="text"
                    wire:model="form.name"
                    @input="handleNameInput($event.target.value)"
                    placeholder="{{ __('example::labels.enter_name', ['name' => 'category']) }}"
                >
            </div>
            @error('form.name')
                <p class="form-error-message">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="form-box required">
            <label for="slug">{{ __('example::labels.slug') }}</label>
            <div class="input-group">
                <input
                    class="form-input font-mono @error('form.slug') has-error @enderror"
                    id="slug"
                    name="slug"
                    type="text"
                    wire:model="form.slug"
                    @input="autoSlug = false"
                    placeholder="{{ __('example::labels.enter_slug', ['name' => 'category']) }}"
                >
                <span
                    class="input-group-item right"
                    x-show="autoSlug"
                    x-transition
                >
                    <span
                        class="rounded bg-brand-100 px-2 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"
                    >
                        {{ __('example::labels.auto') }}
                    </span>
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('example::labels.slug_help') }}
            </p>
            @error('form.slug')
                <p class="form-error-message">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="form-box">
            <label for="description">{{ __('example::labels.description') }}</label>
            <div class="input-group">
                <textarea
                    class="form-input @error('form.description') has-error @enderror"
                    id="description"
                    name="description"
                    rows="4"
                    wire:model="form.description"
                    placeholder="{{ __('example::labels.enter_description', ['name' => 'category']) }}"
                ></textarea>
            </div>
            @error('form.description')
                <p class="form-error-message">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="form-box">
            <label class="mb-3 block">{{ __('example::labels.status') }}</label>
            <x-synapse-toggler
                id="is_active"
                name="is_active"
                wire:model="form.is_active"
                activeColor="green"
            />
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ __('example::labels.inactive_category_help') }}
            </p>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700">
        <button
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
            type="button"
            @click="$dispatch('close-modal-category-form')"
        >
            <span class="ph ph-x mr-1"></span>
            {{ __('example::labels.cancel') }}
        </button>
        <button
            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
            type="button"
            wire:click="save"
        >
            <span class="ph ph-floppy-disk mr-1"></span>
            {{ __('example::labels.save') }}
        </button>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    (() => {
        const register = () => {
        Alpine.data('exampleCategoryForm', () => ({
            autoSlug: true,
            generateSlug(name) {
                if (this.autoSlug) {
                    return name.toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/--+/g, '-')
                        .trim();
                }
            },
            handleNameInput(value) {
                if (this.autoSlug) {
                    this.$wire.form.slug = this.generateSlug(value);
                }
            }
        }));
        };
    document.addEventListener('alpine:init', register);
    if (window.Alpine) {
        register();
    }
    })();
</script>
