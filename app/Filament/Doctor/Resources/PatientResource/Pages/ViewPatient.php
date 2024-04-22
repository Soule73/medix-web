<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    public function getTitle(): string
    {
        return __('doctor/patient.model-label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

        ];
    }
}
