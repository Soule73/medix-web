<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use Filament\Actions;
use App\Models\Doctor;
use App\Enums\Doctor\DoctorStatusEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\DoctorResource;
use App\Jobs\SendConfirmValidationIdentityToDoctrJob;
use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;

class ViewDoctor extends ViewRecord
{
    protected static string $resource = DoctorResource::class;

    public function getTitle(): string
    {
        return __('doctor/doctor.doctor-info');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Actions\Action::make('confrim')
                ->color('success')
                ->label(__('doctor/doctor.doctor-status-validated'))
                ->requiresConfirmation()
                ->action(fn (Doctor $record, array $data) => self::validDoctorIdentity($record, $data))
                ->color('success')
                ->visible($this->record->status !== DoctorStatusEnum::VALIDATED)
                ->icon('heroicon-s-check-circle'),
        ];
    }

    private function validDoctorIdentity(Doctor $record, array $data)
    {
        $docs = $record->documents_for_validations->whereIn(
            'status',
            [DocumentsForValidationStatusEnum::Pending, DocumentsForValidationStatusEnum::NOTVALIDATED]
        )->count();
        if ($docs > 0) {
            $body = $docs > 1 ? __('doctor/doctor.still-more-document', ['docs' => $docs]) :
                __('doctor/doctor.still-one-document');
            Notification::make()
                ->danger()
                ->persistent()
                ->title(__('doctor/doctor.please-validate-documents'))
                ->body($body)
                ->send();
        } else {
            $record->status = DoctorStatusEnum::VALIDATED->value;
            $record->save();
            Notification::make()
                ->title(__('doctor/doctor.doctor-status-approved'))
                ->icon('heroicon-s-check-circle')
                ->success()
                ->send();
            SendConfirmValidationIdentityToDoctrJob::dispatch($record);
        }
    }
}
