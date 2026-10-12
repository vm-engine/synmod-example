<div x-data="{ pageName: 'Accounting posting', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <x-synapse-panel :title="__('example::integrations.accounting')">
        <div class="syn-panel-body space-y-4 px-5 py-4 text-sm sm:px-6">
            <p>{{ __('example::integrations.accounting_intro') }}</p>

            @if ($this->exampleOptions === [])
                <x-synapse-alert type="info" :dismissible="false">{{ __('example::integrations.accounting_no_examples') }}</x-synapse-alert>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="form-box required">
                        <label>{{ __('example::integrations.accounting_record') }}</label>
                        <x-synapse-adv-select wire-model="exampleId" :options="$this->exampleOptions" />
                        @error('exampleId') <p class="form-error-message">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-box required">
                        <label for="accounting-amount">{{ __('example::integrations.accounting_amount') }}</label>
                        <input class="form-input text-right @error('amount') has-error @enderror" id="accounting-amount" type="number" step="0.01" min="0" wire:model="amount">
                        @error('amount') <p class="form-error-message">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-box required">
                        <label>{{ __('example::integrations.accounting_type') }}</label>
                        <x-synapse-adv-select wire-model="type" :options="$this->typeOptions" />
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button class="btn primary" type="button" wire:click="post"><span class="ph ph-check-circle"></span> {{ __('example::integrations.accounting_post') }}</button>
                    <button class="btn" type="button" wire:click="replace"><span class="ph ph-arrows-clockwise"></span> {{ __('example::integrations.accounting_replace') }}</button>
                    <button class="btn" type="button" wire:click="cancel"><span class="ph ph-x-circle"></span> {{ __('example::integrations.accounting_cancel') }}</button>
                </div>

                @if ($result)
                    <div class="space-y-1" data-posting-result>
                        <p>{{ __('example::integrations.accounting_result') }}: <span class="font-semibold">{{ $result['outcome'] }}</span></p>
                        @if ($result['journal'])
                            <p>{{ __('example::integrations.accounting_journal') }}: <a class="font-mono text-brand-600 hover:underline" href="{{ backend_route('accounting.journal.show', ['id' => $result['journal_id']]) }}">{{ $result['journal'] }}</a></p>
                        @endif
                        @if ($result['reason'])
                            <p>{{ __('example::integrations.accounting_held', ['reason' => $result['reason']]) }} <a class="text-brand-600 hover:underline" href="{{ backend_route('accounting.pending_posting.index') }}">→</a></p>
                        @endif
                    </div>
                @endif
            @endif

            <pre class="overflow-x-auto rounded bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800">{{ $this->sampleCode() }}</pre>
        </div>
    </x-synapse-panel>
</div>
