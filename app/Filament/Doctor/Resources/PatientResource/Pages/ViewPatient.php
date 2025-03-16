<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Filament\Doctor\Resources\PatientResource;
use App\Jobs\SendNewAppointmentFollowUpNotificationToPatien;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\WorkingHour;
use App\Models\WorkPlace;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

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
            Actions\EditAction::make()
                ->modalWidth(MaxWidth::Large)
                ->modalHeading(__('actions.edit')),
            Actions\Action::make('new_appointment')
                ->label(__('doctor/notification.follow-up-appointment'))
                ->outlined()
                ->modalWidth(MaxWidth::ExtraLarge)

                ->action(function (Patient $record, array $data) {
                    $doctor = auth()->user()->doctor;
                    $data['patient_id'] = $record->id;
                    $data['doctor_id'] = $doctor->id;
                    $data['status'] = AppointmentStatusEnum::ACCEPTED->value;
                    $data['add_by_doctor'] = true;
                    $data['reschedule_date'] = $data['date_appointment'];

                    $newAppointment = Appointment::create($data);
                    $newAppointment->save();
                    Notification::make()
                        ->title(__('actions.save'))
                        ->icon('heroicon-s-check-circle')
                        ->success()
                        ->send();
                    SendNewAppointmentFollowUpNotificationToPatien::dispatch($newAppointment);
                })
                ->form(function (Form $form) {
                    return $form
                        ->schema(
                            [
                                Forms\Components\DateTimePicker::make('date_appointment')
                                    ->label(__('doctor/notification.follow-up-appointment-date'))
                                    ->required()
                                    ->minDate(now()->addDay())
                                    ->rule(fn () => fn ($attribute, $value, $fail) => self::valideAppointmentDate($attribute, $value, $fail)),
                                Forms\Components\Select::make('work_place_id')
                                    ->required()
                                    ->label(__('doctor/relation/work-place.modelLabel'))
                                    ->createOptionModalHeading('Ajout un lieu de travail')
                                    ->options(WorkPlace::where('doctor_id', auth()->user()->doctor->id)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('MRU')
                                    ->default(auth()->user()->doctor->visit_price)
                                    ->label(__('doctor/notification.follow-up-appointment-price')),
                                Forms\Components\Textarea::make('accepted_message')
                                    ->autofocus()
                                    ->autosize()
                                    ->maxLength(1500)
                                    ->placeholder(__('doctor/appointment.form-accepted-action'))
                                    ->label(__('doctor/appointment.form-accepted-action-label')),
                            ]
                        );
                }),

        ];
    }

    private function valideAppointmentDate($attribute, $value, $fail)
    {
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

        return $fail(__('doctor/appointment.selected-day-or-time-is-not-available-on-your-schedule'));
    }
}
