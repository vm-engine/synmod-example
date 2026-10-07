<div
    x-data="{ pageName: 'Drawer form', isHome: false, drawerOpen: false }"
    @example-drawer-open.window="drawerOpen = true"
    @example-drawer-close.window="drawerOpen = false"
>
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::forms.drawer')">
        <x-slot:toolbar>
            <button
                class="btn primary"
                type="button"
                wire:click="create"
            ><span class="ph ph-plus"></span> {{ __('example::forms.new_example') }}</button>
        </x-slot:toolbar>
        <div class="syn-panel-body">
            <div class="max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>{{ __('example::forms.text') }}</p></th>
                            <th><p>{{ __('example::forms.category') }}</p></th>
                            <th><p>{{ __('example::forms.status') }}</p></th>
                            <th class="datatable-col-actions"><p>{{ __('example::labels.actions') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->examples as $example)
                            <tr wire:key="drawer-row-{{ $example->id }}">
                                <td><p>{{ $example->text }}</p></td>
                                <td><p>{{ $example->category?->name ?? '—' }}</p></td>
                                <td>
                                    <x-synapse-badge
                                        :color="$example->status->color()"
                                        size="sm"
                                    >{{ $example->status->label() }}</x-synapse-badge>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <button
                                            class="btn-icon warning has-tooltip group"
                                            type="button"
                                            wire:click="edit({{ $example->id }})"
                                        >
                                            <span class="ph ph-pencil-simple"></span>
                                            <span class="tooltip">{{ __('example::labels.edit') }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container p-3">{{ $this->examples->links() }}</div>
    </x-synapse-panel>

    <x-synapse-drawer :title="$this->drawerTitle">
        <form
            class="space-y-4"
            wire:submit="save"
        >
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="drawer-text"
                >{{ __('example::forms.text') }}</label>
                <input
                    class="form-input"
                    id="drawer-text"
                    type="text"
                    wire:model="text"
                >
                @error('text')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="drawer-email"
                >{{ __('example::forms.email') }}</label>
                <input
                    class="form-input"
                    id="drawer-email"
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
            <div class="space-y-1">
                <label
                    class="form-label"
                    for="drawer-status"
                >{{ __('example::forms.status') }}</label>
                <select
                    class="form-input"
                    id="drawer-status"
                    wire:model="status"
                >
                    @foreach ($this->statuses as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button
                    class="btn secondary"
                    type="button"
                    @click="drawerOpen = false"
                >{{ __('example::forms.cancel') }}</button>
                <button
                    class="btn primary"
                    type="submit"
                >{{ __('example::forms.save') }}</button>
            </div>
        </form>
    </x-synapse-drawer>
</div>
