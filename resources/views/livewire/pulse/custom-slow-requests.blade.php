<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="{{ __('pulse.slow-requests-header-name') }}"
        title="{{ __('pulse.exception-card-header-title',['time'=>number_format($time),'runAt'=>$runAt]) }}" details="{{ __('pulse.slow-outgoing-requests-header-details',[
                    'threshold'=>($config['threshold'])
                    ,'periodForHumans'=>$this->periodForHumans()])
                    }}">
        <x-slot:icon>
            <x-pulse::icons.arrows-left-right />
        </x-slot:icon>
        <x-slot:actions>
            <x-pulse::select wire:model.live="orderBy" label="{{ __('pulse.sort-by') }}" :options="[
                    'slowest' => __('pulse.slowest'),
                    'count' => __('pulse.count'),
                ]" @change="loading = true" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($slowRequests->isEmpty())
        <x-pulse::no-results />
        @else
        <x-pulse::table>
            <colgroup>
                <col width="0%" />
                <col width="100%" />
                <col width="0%" />
                <col width="0%" />
            </colgroup>
            <x-pulse::thead>
                <tr>
                    <x-pulse::th>{{ __('pulse.method') }}</x-pulse::th>
                    <x-pulse::th>{{ __('pulse.route') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.count') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.slowest') }}</x-pulse::th>
                </tr>
            </x-pulse::thead>
            <tbody>
                @foreach ($slowRequests->take(100) as $slowRequest)
                <tr wire:key="{{ $slowRequest->method.$slowRequest->uri }}-spacer" class="h-2 first:h-0"></tr>
                <tr wire:key="{{ $slowRequest->method.$slowRequest->uri }}-row">
                    <x-pulse::td>
                        <x-pulse::http-method-badge :method="$slowRequest->method" />
                    </x-pulse::td>
                    <x-pulse::td class="overflow-hidden max-w-[1px]">
                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate"
                            title="{{ $slowRequest->uri }}">
                                    {{ $slowRequest->uri }}
                                </code>
                        @if ($slowRequest->action)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate"
                            title="{{ $slowRequest->action }}">
                            {{ $slowRequest->action }}
                        </p>
                        @endif
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        @if ($config['sample_rate'] < 1) <span
                            title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($slowRequest->count)]) }}">
                            ~{{ number_format($slowRequest->count * (1 / $config['sample_rate'])) }}</span>
                            @else
                            {{ number_format($slowRequest->count) }}
                            @endif
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300">
                        @if ($slowRequest->slowest === null)
                        <strong>{{ __('pulse.unknown') }}</strong>
                        @else
                        <strong>{{ number_format($slowRequest->slowest) ?: '<1' }}</strong> ms
                                @endif
                    </x-pulse::td>
                </tr>
                @endforeach
            </tbody>
        </x-pulse::table>

        @if ($slowRequests->count() > 100)
        <div class="mt-2 text-xs text-gray-400 text-center">{{ __('pulse.cache-card-limited-to-entries',['limit'=>100])
            }}</div>
        @endif
        @endif
    </x-pulse::scroll>
</x-pulse::card>