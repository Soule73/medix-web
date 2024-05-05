<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Pages;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Actions;
use Filament\Forms\Form;
use App\Models\Appointment;
use App\Models\WorkingHour;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Enums\Appointment\AppointmentStatusEnum;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Jobs\SendAppointmentStausNotificationToPatient;
use App\Jobs\SendAppointmentPaiementCorfirmedNotificationToPatient;

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
                    if ($record->payed && !$record->confirm_payed) {
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
                                    Forms\Components\DateTimePicker::make('reschedule_date')
                                        ->label(__('doctor/appointment.suggest-another-date-that-would-suit-you'))
                                        ->minDate(now()->addDay())
                                        ->rules([
                                            function () {
                                                return function ($attribute, $value, $fail) {
                                                    $doctorId = auth()->user()->doctor->id;
                                                    $dayOfWeek = Carbon::parse($value)->dayOfWeek();
                                                    $getHour = Carbon::parse($value)->format('H:i');

                                                    $existingHours = WorkingHour::where('doctor_id', $doctorId)
                                                        ->where('day_id', $dayOfWeek)
                                                        ->get();
                                                    foreach ($existingHours as $hour) {
                                                        if ($getHour >= $hour->start_at && $getHour <= $hour->end_at) {
                                                            return null;
                                                        }
                                                    }
                                                    return $fail(__("doctor/appointment.selected-day-or-time-is-not-available-on-your-schedule"));
                                                };
                                            }
                                        ]),
                                    Forms\Components\Textarea::make('accepted_message')
                                        ->autofocus()
                                        ->autosize()
                                        ->maxLength(1500)
                                        ->placeholder(__('doctor/appointment.form-accepted-action'))
                                        ->label(__('doctor/appointment.form-accepted-action-label'))
                                ]
                            );
                        })
                        ->action(function (Appointment $record, array $data) {
                            if ($accepted_message = $data['accepted_message']) {
                                $record->accepted_message = $accepted_message;
                            }
                            if ($data['reschedule_date']) {
                                $record->reschedule_date = $data['reschedule_date'];
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
                        ->visible(
                            !$this->record->add_by_doctor &&
                                $this->record->date_appointment > now()
                                && $this->record->status !== AppointmentStatusEnum::DENIED
                        )
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
