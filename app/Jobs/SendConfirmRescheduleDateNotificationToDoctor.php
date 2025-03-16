<?php

namespace App\Jobs;

use App\Models\Appointment;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendConfirmRescheduleDateNotificationToDoctor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Appointment $appointment)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $doctor = $this->appointment->doctor->user;
        $patientFullName = $this->appointment->patient->user_fullname;
        $doctorDefaultLang = $doctor->default_lang->value;

        if ($this->appointment->add_by_doctor) {
            $body = __('doctor/notification.follow-up-appointment-accepted', ['patient' => $patientFullName], $doctorDefaultLang);
        } else {
            $body = __('doctor/notification.appointment-suggest-date-accepted', ['patient' => $patientFullName], $doctorDefaultLang);
        }

        Notification::make()
            ->title(__('doctor/appointment.accepted-notification'))
            ->body($body)
            ->icon('heroicon-o-bell')
            ->danger()
            ->success()
            ->actions([
                Action::make('view')
                    ->label(__('actions.view'))
                    ->button()
                    ->markAsRead()
                    ->url(route(
                        'filament.doctor.resources.appointments.view',
                        ['record' => $this->appointment->id]
                    )),
            ])
            ->sendToDatabase($doctor);
    }
}
