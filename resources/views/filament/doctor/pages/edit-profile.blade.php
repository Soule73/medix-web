<x-filament-panels::page>
    <x-filament-panels::form wire:submit="updateProfile">
        {{ $this->editProfileForm }}
        <div class="flex items-center justify-start gap-2 ">
            {{--
            <x-filament::loading-indicator class="invisible w-5 h-5" wire:loading.class.add="visible display-flex" />
            --}}
            <x-filament-panels::form.actions :actions="$this->getUpdateProfileFormActions()" />
        </div>
    </x-filament-panels::form>
    <x-filament-panels::form wire:submit="updatePassword">
        {{ $this->editPasswordForm }}
        <x-filament-panels::form.actions :actions="$this->getUpdatePasswordFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>