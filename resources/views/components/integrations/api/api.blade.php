<div x-data="{ pageName: 'API v1', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="space-y-5">
        <x-synapse-panel :title="__('example::integrations.api')">
            <x-slot:toolbar>
                <button
                    class="btn secondary"
                    type="button"
                    wire:click="downloadPostman"
                ><span class="ph ph-download-simple"></span> {{ __('example::integrations.api_download_postman') }}</button>
            </x-slot:toolbar>
            <div class="syn-panel-body space-y-3 px-5 py-4 text-sm sm:px-6">
                <p>{{ __('example::integrations.api_intro') }}</p>
            </div>
            <div class="syn-panel-body max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>{{ __('example::integrations.api_method') }}</p></th>
                            <th><p>{{ __('example::integrations.api_uri') }}</p></th>
                            <th><p>{{ __('example::integrations.api_permission') }}</p></th>
                            <th><p>{{ __('example::integrations.api_signed') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->endpoints as $endpoint)
                            <tr wire:key="endpoint-{{ $loop->index }}">
                                <td><x-synapse-badge size="sm">{{ $endpoint['method'] }}</x-synapse-badge></td>
                                <td><code class="font-mono text-xs">{{ $endpoint['uri'] }}</code></td>
                                <td><code class="font-mono text-xs">{{ $endpoint['permission'] }}</code></td>
                                <td>
                                    @if ($endpoint['signed'])
                                        <x-synapse-badge color="warning" size="sm" icon="ph ph-seal-check">{{ __('example::integrations.api_signed') }}</x-synapse-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::integrations.api_signed')">
            <div class="syn-panel-body space-y-3 px-5 py-4 text-sm sm:px-6">
                <p>{{ __('example::integrations.api_signed_intro') }}</p>
                <pre class="overflow-x-auto rounded bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800">{{ $this->snippets['list'] }}</pre>
                <pre class="overflow-x-auto rounded bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800">{{ $this->snippets['sign'] }}</pre>
                <x-synapse-copy-button :text="$this->snippets['sign']" />
            </div>
        </x-synapse-panel>
    </div>
</div>
