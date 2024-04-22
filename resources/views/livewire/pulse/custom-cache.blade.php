@php
use Illuminate\Support\Str;

$replacements = [
'7 days' => __('pulse.7-days'),
'24 hours' => __('pulse.24-hour'),
'hour' => __('pulse.a-hour'),
'6 hours' => __('pulse.6-hour'),
];

$period = $this->periodForHumans();

foreach ($replacements as $search => $replace) {
$period = Str::replace($search, $replace, $period);
}
@endphp
<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="{{ __('pulse.cache-card-header-name') }}"
        title="{{ __('pulse.cache-card-header-title',['allTime'=>number_format($allTime),'allRunAt'=>$allRunAt,'keyTime'=>number_format($keyTime),'keyRunAt'=>$keyRunAt]) }}"
        details="past {{ $period }}" details="{{
        __('pulse.cache-card-header-details',[
        'periodForHumans'=>$period])
        }}">
        <x-slot:icon>
            <x-pulse::icons.rocket-launch />
        </x-slot:icon>
        <x-slot:actions>
            @php
            $count = count($config['groups']);
            $message = sprintf( __('pulse.cache-card-header-action-message'),
            $count === 1 ? 'is' : 'are',
            $count,
            Str::plural('group', $count)
            );
            @endphp
            <button title="{{ $message }}" @click="alert('{{ str_replace(" \n", '\n' , $message) }}')">
                <x-pulse::icons.information-circle class="w-5 h-5 stroke-gray-400 dark:stroke-gray-600" />
            </button>
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($allCacheInteractions->hits === 0 && $allCacheInteractions->misses === 0)
        <x-custom-no-results />
        @else
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="flex flex-col justify-center @sm:block">
                    <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                        @if ($config['sample_rate'] < 1) <span
                            title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($allCacheInteractions->hits)]) }}">
                            ~{{ number_format($allCacheInteractions->hits * (1 / $config['sample_rate'])) }}</span>
                    @else
                    {{ number_format($allCacheInteractions->hits) }}
                    @endif
                    </span>
                    <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                        {{ __('pulse.hits') }}
                    </span>
                </div>
                <div class="flex flex-col justify-center @sm:block">
                    <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                        @if ($config['sample_rate'] < 1) <span
                            title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($allCacheInteractions->misses)]) }}">
                            ~{{ number_format(($allCacheInteractions->misses) * (1 / $config['sample_rate'])) }}</span>
                    @else
                    {{ number_format($allCacheInteractions->misses) }}
                    @endif
                    </span>
                    <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                        {{ __('pulse.misses') }}
                    </span>
                </div>
                <div class="flex flex-col justify-center @sm:block">
                    <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                        {{ ((int) ($allCacheInteractions->hits / ($allCacheInteractions->hits +
                        $allCacheInteractions->misses) * 10000)) / 100 }}%
                    </span>
                    <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                        {{ __('pulse.hit-rate') }}</ </span>
                </div>
            </div>
            <div>
                <x-pulse::table>
                    <colgroup>
                        <col width="100%" />
                        <col width="0%" />
                        <col width="0%" />
                        <col width="0%" />
                    </colgroup>
                    <x-pulse::thead>
                        <tr>
                            <x-pulse::th>{{ __('pulse.key') }}</x-pulse::th>
                            <x-pulse::th class="text-right">{{ __('pulse.hits') }}</x-pulse::th>
                            <x-pulse::th class="text-right">{{ __('pulse.misses') }}</x-pulse::th>
                            <x-pulse::th class="text-right whitespace-nowrap">{{ __('pulse.hit-rate') }}</x-pulse::th>
                        </tr>
                    </x-pulse::thead>
                    <tbody>
                        @foreach ($cacheKeyInteractions->take(100) as $interaction)
                        <tr wire:key="{{ $interaction->key }}-spacer" class="h-2 first:h-0"></tr>
                        <tr wire:key="{{ $interaction->key }}-row">
                            <x-pulse::td class="max-w-[1px]">
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate"
                                    title="{{ $interaction->key }}">
                                            {{ $interaction->key }}
                                        </code>
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                @if ($config['sample_rate'] < 1) <span
                                    title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($interaction->hits)]) }}">
                                    ~{{ number_format($interaction->hits * (1 / $config['sample_rate'])) }}</span>
                                    @else
                                    {{ number_format($interaction->hits) }}
                                    @endif
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                @if ($config['sample_rate'] < 1) <span
                                    title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($interaction->misses)]) }}">
                                    ~{{ number_format($interaction->misses * (1 / $config['sample_rate'])) }}</span>
                                    @else
                                    {{ number_format($interaction->misses) }}
                                    @endif
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                {{ ((int) ($interaction->hits / ($interaction->hits + $interaction->misses) * 10000)) /
                                100 }}%
                            </x-pulse::td>
                        </tr>
                        @endforeach
                    </tbody>
                </x-pulse::table>

                @if ($cacheKeyInteractions->count() > 100)
                <div class="mt-2 text-xs text-gray-400 text-center">{{
                    __('pulse.cache-card-limited-to-entries',['limit'=>100]) }}</div>
                @endif
            </div>
        </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>