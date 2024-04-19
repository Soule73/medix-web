<?php

namespace App\Filament\Doctor\Resources\DoctorResource\Pages;

use App\Filament\Doctor\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctor extends EditRecord
{
    // protected static ?string $title = "Modifier Mes informations";
    // protected static ?string $navigationLabel = "Modifier Mes informations";

    public function getTitle(): string
    {
        return __('doctor/doctor.edit-record-title');
    }

    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = auth()->user()->doctor;

        static::authorizeResourceAccess();
        $this->fillForm();
    }
}
