<?php

namespace App\Filament\Doctor\Resources\DoctorResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Doctor\Resources\DoctorResource;

class ViewDoctor extends ViewRecord
{
    protected static string $resource = DoctorResource::class;

    public function getTitle(): string
    {
        return __('doctor/doctor.edit-view-title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalHeading(__('doctor/doctor.ressource-form'))
                ->modalWidth(MaxWidth::ExtraLarge)
                ->modalDescription(__('doctor/doctor.ressource-description', ['max' => 1000])),
        ];
    }

    public function mount(int|string|null $record = null): void
    {
        $record = Auth::user()->doctor->id;

        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }
    }
}
