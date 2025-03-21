<?php

namespace App\Jobs;

use App\Models\Appointment;
use Berkayk\OneSignal\OneSignalFacade;
use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendNotificationToDoctorPatient implements ShouldQueue
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
        try {
            $patient = $this->appointment->patient->user;
            $doctor = $this->appointment->doctor->user;
            $oneSignalId = $patient->one_signal_id;

            $patientDefaultLang = $patient->default_lang->value;
            $doctorDefaultLang = $doctor->default_lang->value;

            $doctorNotificationTitle = __('doctor/notification.doctor-new-appointment-title', [], $doctorDefaultLang);
            $doctorNotificationBody = __('doctor/notification.doctor-new-appointment-body', [], $doctorDefaultLang);

            $patientNotificationTitle = __(__('doctor/notification.patient-appointment-pending-title', [], $patientDefaultLang));
            $patientNotificationBody = __(__('doctor/notification.patient-appointment-pending-body', ['id' => '#' . Str::padLeft(strval($this->appointment->id), 8, '0')], $patientDefaultLang));

            Notification::make()
                ->title($doctorNotificationTitle)
                ->body($doctorNotificationBody)
                ->icon('heroicon-o-bell')
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

            Notification::make()
                ->title($patientNotificationTitle)
                ->body($patientNotificationBody)
                ->icon('heroicon-o-bell')
                ->success()
                ->viewData(['record' => $this->appointment->id])
                ->sendToDatabase($patient);

            if ($oneSignalId) {
                $params = [];
                $params['small_icon'] = 'ic_stat_onesignal_default'; // icon res name specified in your app
                OneSignalFacade::addParams($params)->sendNotificationToUser(
                    message: $patientNotificationBody,
                    userId: $oneSignalId,
                    headings: $patientNotificationTitle,
                );
            }
        } catch (Exception $e) {
            Log::error('Error SendNotificationToDoctorPatient: ' . $e->getMessage());
        }
    }
}
