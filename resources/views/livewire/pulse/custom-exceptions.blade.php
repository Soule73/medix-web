<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="{{ __('pulse.exception-card-header-name') }}"
        title="{{ __('pulse.exception-card-header-title',['time'=>number_format($time),'runAt'=>$runAt]) }}" details="{{
        __('pulse.cache-card-header-details',[
        'periodForHumans'=>$this->periodForHumans()])
        }}">
        <x-slot:icon>
            <x-pulse::icons.bug-ant />
        </x-slot:icon>
        <x-slot:actions>
            <x-pulse::select wire:model.live="orderBy" label="{{ __('pulse.sort-by') }}" :options="[
                    'count' => __('pulse.count'),
                    'latest' => __('pulse.latest'),
                ]" @change="loading = true" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        <div class="min-h-full flex flex-col">
            @if ($exceptions->isEmpty())
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
                        <x-pulse::th>Type</x-pulse::th>
                        <x-pulse::th class="text-right">{{ __('pulse.latest') }}</x-pulse::th>
                        <x-pulse::th class="text-right">{{ __('pulse.count') }}</x-pulse::th>
                    </tr>
                </x-pulse::thead>
                <tbody>
                    @foreach ($exceptions->take(100) as $exception)
                    <tr wire:key="{{ $exception->class.$exception->location }}-spacer" class="h-2 first:h-0"></tr>
                    <tr wire:key="{{ $exception->class.$exception->location }}-row">
                        <x-pulse::td class="max-w-[1px]">
                            <code class="block text-xs text-gray-900 dark:text-gray-100 truncate"
                                title="{{ $exception->class }}">
                                        {{ $exception->class }}
                                    </code>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate"
                                title="{{ $exception->location }}">
                                {{ $exception->location }}
                            </p>
                        </x-pulse::td>
                        <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                            {{ $exception->latest->ago(syntax: Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}
                        </x-pulse::td>
                        <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                            @if ($config['sample_rate'] < 1) <span
                                title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($exception->count)]) }}">
                                ~{{ number_format($exception->count * (1 / $config['sample_rate'])) }}</span>
                                @else
                                {{ number_format($exception->count) }}
                                @endif
                        </x-pulse::td>
                    </tr>
                    @endforeach
                </tbody>
            </x-pulse::table>
            @endif

            @if ($exceptions->count() > 100)
            <div class="mt-2 text-xs text-gray-400 text-center">{{
                __('pulse.cache-card-limited-to-entries',['limit'=>100]) }}</div>
            @endif
        </div>
    </x-pulse::scroll>
</x-pulse::card>