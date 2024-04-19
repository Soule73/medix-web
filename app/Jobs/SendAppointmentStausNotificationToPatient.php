<?php

namespace App\Jobs;

use Exception;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Berkayk\OneSignal\OneSignalFacade;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Enums\Appointment\AppointmentStatusEnum;

class SendAppointmentStausNotificationToPatient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Appointment $appointment)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $patient = $this->appointment->patient->user;

            $oneSignald = $patient->one_signal_id;
            $default_lang = $patient->default_lang->value;
            $id = "#" . Str::padLeft($this->appointment->id, 8, '0');
            $status = __('doctor/notification.appointment-id', ['id' => $id], $default_lang ?? config('app.locale'));

            $doctor = $this->appointment->doctor->user_fullname . " " . $this->appointment->doctor->user_fullname;
            $body_accepted = __('doctor/notification.appointment-accepted', ['doctor' => $doctor], $default_lang ?? config('app.locale'));
            $body_refused = __('doctor/notification.appointment-refused', ['doctor' => $doctor], $default_lang ?? config('app.locale'));
            $body_finished = __('doctor/notification.appointment-finished', ['doctor' => $doctor], $default_lang ?? config('app.locale'));

            if ($this->appointment->status === AppointmentStatusEnum::ACCEPTED) {

                Notification::make()
                    ->title($status)
                    ->body($body_accepted)
                    ->icon('heroicon-o-bell')
                    ->success()
                    ->viewData(["record" => $this->appointment->id])
                    ->sendToDatabase($patient);

                if ($oneSignald) {
                    $params = [];
                    $params['small_icon'] = 'ic_stat_onesignal_default';
                    OneSignalFacade::addParams($params)->sendNotificationToUser(
                        message: $body_accepted,
                        userId: $oneSignald,
                        headings: $status,
                    );
                }
            } else if ($this->appointment->status === AppointmentStatusEnum::FINISHED) {
                Notification::make()
                    ->title($status)
                    ->body($body_finished)
                    ->icon('heroicon-o-bell')
                    ->success()
                    ->viewData(["record" => $this->appointment->id])
                    ->sendToDatabase($patient);

                if ($oneSignald) {
                    $params = [];
                    $params['small_icon'] = 'ic_stat_onesignal_default';
                    OneSignalFacade::addParams($params)->sendNotificationToUser(
                        message: $body_finished,
                        userId: $oneSignald,
                        headings: $status,
                    );
                }
            } else {

                Notification::make()
                    ->title($status)
                    ->body($body_refused)
                    ->icon('heroicon-o-bell')
                    ->danger()
                    ->viewData(["record" => $this->appointment->id])
                    ->sendToDatabase($this->appointment->patient->user);

                if ($oneSignald) {
                    $params = [];
                    $params['small_icon'] = 'ic_stat_onesignal_default'; // icon res name specified in your app
                    OneSignalFacade::addParams($params)->sendNotificationToUser(
                        message: $body_refused,
                        userId: $oneSignald,
                        headings: $status,
                    );
                }
            }
        } catch (Exception $e) {
            Log::error("Error SendAppointmentStausNotificationToPatient: " . $e->getMessage());
        }
    }
}
