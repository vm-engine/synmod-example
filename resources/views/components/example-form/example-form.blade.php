<div
    class="relative overflow-y-auto"
    x-data="{ pageName: 'Example Form', isHome: false }"
>
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])
    <div class="mb-16 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form 1</h3>
                </div>
                <div class="card-body">
                    <div class="form-box">
                        <label for="category">Category</label>
                        <x-synapse-adv-select
                            wire-model="form.category_id"
                            :options="$this->categories"
                            :multiple="false"
                            placeholder="Select a category"
                        />
                        @error('form.category_id')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box required">
                        <label for="textbox">{{ __('example::labels.forms.text') }}</label>
                        <div class="input-group">
                            <input
                                class="form-input @error('form.text') has-error @enderror"
                                id="textbox"
                                name="textbox"
                                type="text"
                                wire:model="form.text"
                            >
                        </div>
                        @error('form.text')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box required">
                        <label for="email">{{ __('example::labels.forms.email') }}</label>
                        <div class="input-group">
                            <span class="input-group-item left">
                                <span class="fa-solid fa-envelope"></span>
                            </span>
                            <input
                                class="form-input pl-[62px]! @error('form.email') has-error @enderror"
                                id="email"
                                name="email"
                                type="email"
                                wire:model="form.email"
                            >
                        </div>
                        @error('form.email')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box required">
                        <label for="number">{{ __('example::labels.forms.number') }}</label>
                        <div class="relative">
                            <input
                                class="form-input @error('form.number') has-error @enderror"
                                id="number"
                                name="number"
                                type="number"
                                wire:model="form.number"
                                min="0"
                            >
                        </div>
                        @error('form.number')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box">
                        <label for="currency">{{ __('example::labels.forms.masked') }}</label>
                        <x-synapse-currency
                            id="masked"
                            name="masked"
                            :currency="'Rp.'"
                            wire:model="form.masked"
                        ></x-synapse-currency>
                    </div>
                    <div class="form-box required">
                        <label for="dropdown">{{ __('example::labels.forms.dropdown') }}</label>
                        <x-synapse-select
                            id="dropdown"
                            name="dropdown"
                            :options="$this->options"
                            wire:model="form.dropdown"
                        ></x-synapse-select>
                        @error('form.dropdown')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box required">
                        <label for="multidropdown">{{ __('example::labels.forms.multidropdown') }}</label>
                        <x-synapse-multiselect
                            class="@error('form.multidropdown') has-error @enderror"
                            :name="'multidropdown'"
                            :options="$this->options"
                            :model="$form->multidropdown"
                            @class([
                                'has-error' => $errors->has('form.multidropdown'),
                            ])
                            wire:model="form.multidropdown"
                        ></x-synapse-multiselect>
                        @error('form.multidropdown')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box">
                        <label for="date">{{ __('example::labels.forms.date') }}</label>
                        <x-synapse-datepicker
                            id="date"
                            name="date"
                            :flatpickr="'{}'"
                            wire:model="form.date"
                        ></x-synapse-datepicker>
                    </div>
                    <div class="form-box">
                        <label for="datetime">{{ __('example::labels.forms.datetime') }}</label>
                        @php
                            $flatpickr = json_encode([
                                'enableTime' => true,
                                'time_24hr' => true,
                                'dateFormat' => 'Y-m-d H:i',
                            ]);
                        @endphp
                        <x-synapse-datepicker
                            id="datetime"
                            name="datetime"
                            :flatpickr="$flatpickr"
                            wire:model="form.datetime"
                        ></x-synapse-datepicker>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form 2</h3>
                </div>
                <div class="card-body">
                    <div class="form-box required">
                        <label for="protected">{{ __('example::labels.forms.protected') }}</label>
                        <x-synapse-password
                            class="@error('form.protected') has-error @enderror"
                            id="protected"
                            name="protected"
                            @class([
                                'has-error' => $errors->has('form.protected'),
                            ])
                            wire:model="form.protected"
                        ></x-synapse-password>
                        @error('form.protected')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box">
                        <label for="textarea">{{ __('example::labels.forms.textarea') }}</label>
                        <div class="input-group">
                            <textarea
                                class="form-input"
                                id="textarea"
                                name="textarea"
                                type="text"
                                rows="6"
                                wire:model="form.textarea"
                            ></textarea>
                        </div>
                    </div>
                    <div class="form-box">
                        <label for="errortest">{{ __('example::labels.forms.error_test') }}</label>
                        <div class="input-group">
                            <input
                                class="form-input has-error"
                                name="errortest"
                                type="text"
                            >
                            <span class="error-icon">
                                <span class="fa-solid fa-circle-exclamation"></span>
                            </span>
                        </div>
                        <p class="form-error-message">
                            Invalid Input
                        </p>
                    </div>
                    <div class="form-box">
                        <label>{{ __('example::labels.forms.radio') }}</label>
                        <x-synapse-radio-buttons
                            :name="'radio'"
                            :options="range(1, 3)"
                            wire:model="form.radio"
                            :model="$form->radio"
                        ></x-synapse-radio-buttons>
                    </div>
                    <div class="form-box required">
                        <label>{{ __('example::labels.forms.checkbox') }}</label>
                        <x-synapse-checkboxes
                            :name="'checkbox'"
                            :options="range(1, 5)"
                            @class([
                                'has-error' => $errors->has('form.checkbox'),
                            ])
                            wire:model="form.checkbox"
                            :model="$form->checkbox"
                        ></x-synapse-checkboxes>
                        @error('form.checkbox')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box">
                        <label for="file">{{ __('example::labels.forms.file') }}</label>
                        <input
                            class="form-input"
                            id="file"
                            name="file"
                            type="file"
                            wire:model="form.file"
                            accept=".doc,.docx,.pdf,.xls,.xlsx,.ppt,.pptx"
                        />
                        @error('file')
                            <p class="form-error-message">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="form-box">
                        <label for="color">{{ __('example::labels.forms.color') }}</label>
                        <input
                            class="form-input"
                            id="color"
                            name="color"
                            type="color"
                            wire:model="form.color"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card z-9 fixed bottom-0 right-0 m-5 shadow-gray-700">
        <button
            class="btn primary"
            id="save"
            type="button"
            @click="() => {
                    $wire.form.masked = $wire.form.masked.replace(/\./g, '');
                    $wire.save();
                }"
        ><span class="fa-solid fa-floppy-disk"></span> Save</button>
    </div>
</div>
