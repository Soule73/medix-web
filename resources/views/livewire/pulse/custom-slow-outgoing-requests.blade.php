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
    <x-pulse::card-header name="{{ __('pulse.slow-outgoing-requests-header-name') }}"
        title="{{ __('pulse.exception-card-header-title',['time'=>number_format($time),'runAt'=>$runAt]) }}" details="{{
        __('pulse.slow-outgoing-requests-header-details',[
        'threshold'=>($config['threshold'])
        ,'periodForHumans'=>$period])
        }}">
        <x-slot:icon>
            <x-pulse::icons.cloud-arrow-up />
        </x-slot:icon>
        <x-slot:actions>
            @php
            $count = count($config['groups']);
            $message = sprintf(
            __('pulse.slow-outgoing-requests-header-action-message',['s0'=>'%s','d'=>'%d','s1'=>'%s']),
            $count === 1 ? __('pulse.is') : __('pulse.are'),
            $count,
            Str::plural('group', $count)
            );
            @endphp
            <button title="{{ $message }}" @click="alert('{{ str_replace(" \n", '\n' , $message) }}')">
                <x-pulse::icons.information-circle class="w-5 h-5 stroke-gray-400 dark:stroke-gray-600" />
            </button>

            <x-pulse::select wire:model.live="orderBy" label="{{ __('pulse.sort-by') }}" :options="[
                    'slowest' => __('pulse.slowest'),
                    'count' => __('pulse.count'),
                ]" @change="loading = true" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($slowOutgoingRequests->isEmpty())
        <x-custom-no-results />
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
                    <x-pulse::th>{{ __('pulse.uri') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.count') }}</x-pulse::th>
                    <x-pulse::th class="text-right">{{ __('pulse.slowest') }}</x-pulse::th>
                </tr>
            </x-pulse::thead>
            <tbody>
                @foreach ($slowOutgoingRequests->take(100) as $request)
                <tr wire:key="{{ $request->method.$request->uri }}-spacer" class="h-2 first:h-0"></tr>
                <tr wire:key="{{ $request->method.$request->uri }}-row">
                    <x-pulse::td>
                        <x-pulse::http-method-badge :method="$request->method" />
                    </x-pulse::td>
                    <x-pulse::td class="max-w-[1px]">
                        <div class="flex items-center" title="{{ $request->uri }}">
                            @if ($host = parse_url($request->uri, PHP_URL_HOST))
                            <img wire:ignore src="https://unavatar.io/{{ $host }}?fallback=false" loading="lazy"
                                class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                            @endif
                            <code class="block text-xs text-gray-900 dark:text-gray-100 truncate">
                                        {{ $request->uri }}
                                    </code>
                        </div>
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        @if ($config['sample_rate'] < 1) <span
                            title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$config['sample_rate'],'raw_value'=>number_format($request->count)]) }}">
                            ~{{ number_format($request->count * (1 / $config['sample_rate'])) }}</span>
                            @else
                            {{ number_format($request->count) }}
                            @endif
                    </x-pulse::td>
                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300">
                        @if ($request->slowest === null)
                        <strong>Unknown</strong>
                        @else
                        <strong>{{ number_format($request->slowest) ?: '<1' }}</strong> ms
                                @endif
                    </x-pulse::td>
                </tr>
                @endforeach
            </tbody>
        </x-pulse::table>

        @if ($slowOutgoingRequests->count() > 100)
        <div class="mt-2 text-xs text-gray-400 text-center">{{ __('pulse.cache-card-limited-to-entries',['limit'=>100])
            }}</div>
        @endif
        @endif
    </x-pulse::scroll>
</x-pulse::card>