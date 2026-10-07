<div class="max-w-full overflow-x-auto">
    <table class="datatable min-w-full">
        <thead>
            <tr>
                <th><p>{{ __('example::pages.category') }}</p></th>
                @foreach ($statuses as $status)
                    <th class="text-right"><p>{{ $status['label'] }}</p></th>
                @endforeach
                <th class="text-right"><p>{{ __('example::pages.total') }}</p></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($matrix['rows'] as $row)
                <tr wire:key="matrix-{{ $loop->index }}">
                    <td><p>{{ $row['category'] }}</p></td>
                    @foreach ($statuses as $status)
                        <td class="text-right"><p>{{ $row['counts'][$status['value']] }}</p></td>
                    @endforeach
                    <td class="text-right"><p class="font-semibold">{{ $row['total'] }}</p></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($statuses) + 2 }}">
                        <p class="py-6 text-center text-sm text-gray-500">{{ __('example::pages.no_results_text') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th><p>{{ __('example::pages.total') }}</p></th>
                @foreach ($statuses as $status)
                    <th class="text-right"><p>{{ $matrix['totals'][$status['value']] }}</p></th>
                @endforeach
                <th class="text-right"><p>{{ $matrix['total'] }}</p></th>
            </tr>
        </tfoot>
    </table>
</div>
