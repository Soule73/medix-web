<?php

namespace App\Jobs;

use Berkayk\OneSignal\OneSignalFacade;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RemindPatientForAppointment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Collection $appointments)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->appointments as $appointment) {
            try {
                $patient = $appointment->patient->user;

                $oneSignalId = $patient->one_signal_id;
                $patientDefaultLang = $patient->default_lang->value;
                $id = '#' . Str::padLeft(strval($appointment->id), 8, '0');
                $status = __('doctor/notification.appointment-id', ['id' => $id], $patientDefaultLang);
                $bodyRemind = __('doctor/notification.remind-patient-notification');

                if ($oneSignalId) {
                    // send notification if user as subricuber in notifcations
                    $params = ['small_icon' => 'ic_stat_onesignal_default'];
                    OneSignalFacade::addParams($params)->sendNotificationToUser(
                        message: $bodyRemind,
                        userId: $oneSignalId,
                        headings: $status,
                    );

                    // set remind_patient column to true
                    $appointment->remind_patient = true;
                    $appointment->save();
                }
            } catch (Exception $e) {
                Log::error('Error RemindPatientForAppointment: ' . $e->getMessage());
            }
        }
    }
}
