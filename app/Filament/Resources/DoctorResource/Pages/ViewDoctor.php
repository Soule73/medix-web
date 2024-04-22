<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Enums\Doctor\DoctorStatusEnum;
use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;
use App\Filament\Resources\DoctorResource;
use App\Models\Doctor;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

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
                ->action(function (Doctor $record, array $data) {
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
                        $recipient = $record->user;
                        $default_lang = $recipient->default_lang->value;
                        $record->status = DoctorStatusEnum::VALIDATED->value;
                        $record->save();
                        Notification::make()
                            ->title(__('doctor/doctor.doctor-status-approved'))
                            ->icon('heroicon-s-check-circle')
                            ->success()
                            ->send();
                        Notification::make()
                            ->title(
                                __('doctor/doctor.approved-notification-title', [], $default_lang ?? config('app.locale'))
                            )
                            ->body(
                                __('doctor/doctor.approved-notification-body', [], $default_lang ?? config('app.locale'))
                            )
                            ->icon('heroicon-s-check-badge')
                            ->success()

                            ->sendToDatabase($recipient);
                    }
                })
                ->color('success')
                ->visible(
                    $this->record->status !== DoctorStatusEnum::VALIDATED
                )
                ->icon('heroicon-s-check-circle'),
        ];
    }
}
