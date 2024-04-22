<?php

namespace App\Filament\Doctor\Resources\DoctorResource\Pages;

use App\Filament\Doctor\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

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
        $record = auth()->user()->doctor->id;

        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }
    }
}
