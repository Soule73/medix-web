<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="{{ __('pulse.slow-jobs-card-header-name') }}"
        title="{{ __('pulse.exception-card-header-title',['time'=>number_format($time, 0),'runAt'=>$runAt]) }}" details="{{
        __('pulse.slow-outgoing-requests-header-details',[
        'threshold'=>($config['threshold'])
        ,'periodForHumans'=>$this->periodForHumans()])
        }}">
        <x-slot:icon>
            <x-pulse::icons.command-line />
        </x-slot:icon>
        <x-slot:actions>
            <x-pulse::select wire:model.live="orderBy" label="{{ __('pulse.sort-by') }}" :options="[
                    'slowest' => __('pulse.slowest'),
                    'count' => __('pulse.count'),
                ]" @change="loading = true" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($slowJobs->isEmpty())
        <x-pulse::no-results />
        @else
        <x-pulse::table>
            <colgroup>
                <col width="100%" />
                <col width="0%" />
                <col width="0%" />
            </colgroup>
            <x-pulse::thead>
                <tr>
                    <x-pulse::th>{{ __('pulse.job') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.count') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.slowest') }}</x-pulse::th>
                </tr>
            </x-pulse::thead>
            <tbody>
                @foreach ($slowJobs->take(100) as $job)
                <tr wire:key="{{ $job->job }}-spacer" class="h-2 first:h-0"></tr>
                <tr wire:key="{{ $job->job }}-row">
                    <x-pulse::td class="max-w-[1px]">
                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="{{ $job->job }}">
                                    {{ $job->job }}
                                </code>
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        @if ($config['sample_rate'] < 1) <span
                            title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($job->count)]) }}">
                            ~{{ number_format($job->count * (1 / $config['sample_rate'])) }}</span>
                            @else
                            {{ number_format($job->count) }}
                            @endif
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300">
                        @if ($job->slowest === null)
                        <strong>{{ __('pulse.unknown') }}</strong>
                        @else
                        <strong>{{ number_format($job->slowest) ?: '<1' }}</strong> ms
                                @endif
                    </x-pulse::td>
                </tr>
                @endforeach
            </tbody>
        </x-pulse::table>
        @endif

        @if ($slowJobs->count() > 100)
        <div class="mt-2 text-xs text-gray-400 text-center">{{ __('pulse.cache-card-limited-to-entries',['limit'=>100])
            }}</div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>