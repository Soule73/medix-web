<div class=" !w-full flex justify-end gap-4 font-bold flex-wrap items-center">
    <div>
        {{ __('pulse.period') }} :
    </div>
    <div>
        <x-filament::tabs x-data="{
                            setPeriod(period) {
                                let query = new URLSearchParams(window.location.search)
                                if (period === '1_hour') {
                                    query.delete('period')
                                } else {
                                    query.set('period', period)
                                }

                                window.location = `${location.pathname}?${query}`
                            }}">
            <x-filament::tabs.item :active="$period === '1_hour'" @click="setPeriod('1_hour')">
                1{{ __('pulse.hour') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === '6_hours'" @click="setPeriod('6_hours')">
                6{{ __('pulse.hours') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === '24_hours'" @click="setPeriod('24_hours')">
                24{{ __('pulse.hours') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === '7_days'" @click="setPeriod('7_days')">
                7{{ __('pulse.days') }}
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>
</div>