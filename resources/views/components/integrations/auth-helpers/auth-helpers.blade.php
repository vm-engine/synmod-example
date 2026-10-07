<div x-data="{ pageName: 'Auth helpers', isHome: false }">
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <div class="space-y-5">
        <x-synapse-panel :title="__('example::integrations.directive')">
            <div class="syn-panel-body max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <thead>
                        <tr>
                            <th><p>{{ __('example::integrations.directive') }}</p></th>
                            <th><p>{{ __('example::integrations.result') }}</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['canAccess'] }}</code></td>
                            <td>
                                @canAccess('example.manage.delete')
                                    <x-synapse-badge color="success" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endcanAccess
                                @unlessCanAccess('example.manage.delete')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endCanAccess
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['unlessCanAccess'] }}</code></td>
                            <td>
                                @unlessCanAccess('example.manage.delete')
                                    <x-synapse-badge color="success" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endCanAccess
                                @canAccess('example.manage.delete')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endcanAccess
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['hasRole'] }}</code></td>
                            <td>
                                @hasRole('admin')
                                    <x-synapse-badge color="success" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endhasRole
                                @unlessHasRole('admin')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endHasRole
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['hasAnyRole'] }}</code></td>
                            <td>
                                @hasAnyRole('admin|editor')
                                    <x-synapse-badge color="success" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endHasAnyRole
                                @unlessHasAnyRole('admin|editor')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endHasAnyRole
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['isDev'] }}</code></td>
                            <td>
                                @isDev
                                    <x-synapse-badge color="success" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endisDev
                                @unlessIsDev
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endIsDev
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['isDenied'] }}</code></td>
                            <td>
                                @isDenied('example.manage.delete')
                                    <x-synapse-badge color="danger" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endIsDenied
                                @unlessIsDenied('example.manage.delete')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endIsDenied
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-mono text-xs">{{ $this->snippets['hasDirectPermission'] }}</code></td>
                            <td>
                                @hasDirectPermission('example.manage.delete')
                                    <x-synapse-badge color="info" size="sm">{{ __('example::integrations.shown') }}</x-synapse-badge>
                                @endHasDirectPermission
                                @unlessHasDirectPermission('example.manage.delete')
                                    <x-synapse-badge size="sm">{{ __('example::integrations.hidden') }}</x-synapse-badge>
                                @endHasDirectPermission
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::integrations.helper')">
            <div class="syn-panel-body max-w-full overflow-x-auto">
                <table class="datatable min-w-full">
                    <tbody>
                        @foreach ($this->helperResults() as $helper)
                            <tr wire:key="helper-{{ $loop->index }}">
                                <td><code class="font-mono text-xs">{{ $helper['call'] }}</code></td>
                                <td>
                                    @if (is_bool($helper['result']))
                                        <x-synapse-badge
                                            :color="$helper['result'] ? 'success' : 'gray'"
                                            size="sm"
                                        >{{ $helper['result'] ? __('example::integrations.yes') : __('example::integrations.no') }}</x-synapse-badge>
                                    @else
                                        <p>{{ $helper['result'] }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-synapse-panel>

        <x-synapse-panel :title="__('example::integrations.guarded_title')">
            <div class="syn-panel-body space-y-3 px-5 py-4 text-sm sm:px-6">
                <p>{{ __('example::integrations.guarded_text') }}</p>
                <pre class="overflow-x-auto rounded bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800">{{ $this->snippets['guard'] }}</pre>
                <button
                    class="btn primary"
                    type="button"
                    wire:click="guardedDemo"
                >{{ __('example::integrations.guarded_button') }}</button>
            </div>
        </x-synapse-panel>
    </div>
</div>
