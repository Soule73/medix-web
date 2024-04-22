<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Pages;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Jobs\SendAppointmentPaiementCorfirmedNotificationToPatient;
use App\Jobs\SendAppointmentStausNotificationToPatient;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public function getTitle(): string
    {
        return __('doctor/appointment.model-label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->after(function (Appointment $record) {
                    if ($record->payed && ! $record->confirm_payed) {
                        SendAppointmentPaiementCorfirmedNotificationToPatient::dispatch(appointment: $record);
                    }
                })
                ->visible(fn () => $this->record->date_appointment <= now() && $this->record->status === AppointmentStatusEnum::ACCEPTED),
            Actions\DeleteAction::make()
                ->icon('heroicon-s-x-circle')
                ->visible($this->record->date_appointment <= now() && $this->record->status === AppointmentStatusEnum::PENDING),
            Actions\Action::make(__('doctor/appointment.view-patient'))
                ->visible($this->record->status === AppointmentStatusEnum::ACCEPTED)

                ->url(route('filament.doctor.resources.patients.view', ['record' => $this->record->patient->id])),
            Actions\ActionGroup::make(
                [
                    Actions\Action::make('accept')
                        ->label(__('doctor/appointment.action-accepted'))
                        ->requiresConfirmation()
                        ->form(function (Form $form) {
                            return $form->schema(
                                [
                                    Forms\Components\Textarea::make('accepted_message')
                                        ->autofocus()
                                        ->autosize()
                                        ->maxLength(1500)
                                        ->placeholder(__('doctor/appointment.form-accepted-action'))
                                        ->label(__('doctor/appointment.form-accepted-action-label')),
                                ]
                            );
                        })
                        ->action(function (Appointment $record, array $data) {

                            if ($accepted_message = $data['accepted_message']) {
                                $record->accepted_message = $accepted_message;
                            }
                            $record->status = AppointmentStatusEnum::ACCEPTED->value;
                            $record->save();
                            Notification::make()
                                ->title(__('doctor/appointment.accepted-notification'))
                                ->icon('heroicon-s-check-circle')
                                ->success()
                                ->send();
                            SendAppointmentStausNotificationToPatient::dispatch(appointment: $record);
                        })
                        ->color('success')
                        ->visible(
                            $this->record->date_appointment > now()
                                && $this->record->status !== AppointmentStatusEnum::ACCEPTED
                        )
                        ->icon('heroicon-s-check-circle'),

                    Actions\Action::make('finished')
                        ->label(__('doctor/appointment.finished'))
                        ->visible(
                            $this->record->date_appointment <= now()
                                && $this->record->status === AppointmentStatusEnum::ACCEPTED
                        )
                        // ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->action(function (Appointment $records) {
                            $records->status = AppointmentStatusEnum::FINISHED->value;
                            $records->save();
                            Notification::make()
                                ->title(__('doctor/appointment.finished-notification'))
                                ->icon('heroicon-s-check-circle')
                                ->info()
                                ->send();
                            SendAppointmentStausNotificationToPatient::dispatch(appointment: $records);
                        })
                        ->color('info')
                        ->icon('heroicon-s-check-circle'),

                    Actions\Action::make('refuse')
                        ->label(__('doctor/appointment.action-refused'))
                        ->visible($this->record->date_appointment > now()
                            && $this->record->status !== AppointmentStatusEnum::DENIED)
                        ->requiresConfirmation()
                        ->action(function (Appointment $record, array $data) {
                            $record->status = AppointmentStatusEnum::DENIED->value;
                            $record->reason_for_refusal = $data['reason_for_refusal'];
                            $record->save();
                            Notification::make()
                                ->title(__('doctor/appointment.refused-notification'))
                                ->icon('heroicon-s-x-circle')
                                ->danger()
                                ->send();
                            SendAppointmentStausNotificationToPatient::dispatch(appointment: $record);
                        })
                        ->form(function (Form $form) {
                            return $form->schema(
                                [
                                    Forms\Components\Textarea::make('reason_for_refusal')
                                        ->required()
                                        ->autofocus()
                                        ->autosize()
                                        ->maxLength(1500)
                                        ->placeholder(__('doctor/appointment.form-refused-action'))
                                        ->label(__('doctor/appointment.form-refused-action-label'))
                                        ->validationMessages([
                                            'required' => __('doctor/appointment.form-refused-action'),
                                        ]),
                                ]
                            );
                        })
                        ->color('danger')
                        ->icon('heroicon-s-x-circle'),
                ]
            )
                // ->visible($this->record->date_appointment > now())
                ->button()
                ->label(__('actions.actions')),

        ];
    }
}
