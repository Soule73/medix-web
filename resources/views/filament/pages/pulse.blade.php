<x-filament-panels::page>
    <livewire:pulse.custom-period-selector />
    <div class="mx-auto grid default:grid-cols-6 default:gap-6 container">
        <livewire:pulse.servers cols="full" />

        <livewire:pulse.custom-usage cols="2" rows="2" />

        <livewire:pulse.custom-slow-requests cols="4" />

        <livewire:pulse.custom-exceptions cols="4" />

        <livewire:pulse.custom-queues cols="3" />

        <livewire:pulse.custom-cache cols="3" />

        <livewire:pulse.custom-slow-queries cols="6" />

        <livewire:pulse.custom-slow-jobs cols="3" />

        <livewire:pulse.custom-slow-outgoing-requests cols="3" />
    </div>

</x-filament-panels::page>