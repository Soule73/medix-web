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

    <x-pulse::card-header :name="match ($this->type) {
            'requests' => __('pulse.top-users-making-requests',['count'=>10]),
        'slow_requests' => __('pulse.top-users-experiencing-slow-endpoints',['count'=>10]),
        'jobs' => __('pulse.top-users-dispatching-jobs',['count'=>10]),
        default => __('pulse.application-usage')
        }" title="{{ __('pulse.exception-card-header-title',['time'=>number_format($time),'runAt'=>$runAt]) }}"
        details="{{($this->usage === 'slow_requests') ? __('pulse.slow-outgoing-requests-header-details',
            ['threshold'=>$slowRequestsConfig['threshold'],'periodForHumans'=>$period]
            ) : __('pulse.cache-card-header-details',['periodForHumans'=>$period])
                }}">
        <x-slot:icon>
            <x-dynamic-component :component="'pulse::icons.' . match ($this->type) {
                'requests' => 'arrow-trending-up',
                'slow_requests' => 'clock',
                'jobs' => 'scale',
                default => 'cursor-arrow-rays'
            }" />
        </x-slot:icon>
        <x-slot:actions>
            @if (! $this->type)
            <x-pulse::select wire:model.live="usage" label="{{ __('pulse.top-users',['count'=>10]) }}" :options="[
                        'requests' => __('pulse.making-requests'),
                        'slow_requests' => __('pulse.experiencing-slow-endpoints'),
                        'jobs' => __('pulse.dispatching-jobs'),
                    ]" class="flex-1" @change="loading = true" />
            @endif
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($userRequestCounts->isEmpty())
        <x-custom-no-results />
        @else
        <div class="grid grid-cols-1 @lg:grid-cols-2 @3xl:grid-cols-3 @6xl:grid-cols-4 gap-2">
            @php
            $sampleRate = match($this->usage) {
            'requests' => $userRequestsConfig['sample_rate'],
            'slow_requests' => $slowRequestsConfig['sample_rate'],
            'jobs' => $jobsConfig['sample_rate'],
            };
            @endphp

            @foreach ($userRequestCounts as $userRequestCount)
            <x-pulse::user-card wire:key="{{ $userRequestCount->key }}" :user="$userRequestCount->user">
                <x-slot:stats>
                    @if ($sampleRate < 1) <span
                        title="{{ __('pulse.cache-card-sample-rate-raw',['sample_rate'=>$sampleRate,'raw_value'=>number_format($userRequestCount->count)]) }}">
                        ~{{ number_format($userRequestCount->count * (1 / $sampleRate)) }}</span>
                        @else
                        {{ number_format($userRequestCount->count) }}
                        @endif
                </x-slot:stats>
            </x-pulse::user-card>
            @endforeach
        </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>