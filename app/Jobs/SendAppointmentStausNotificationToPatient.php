<?php

namespace App\Jobs;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\Appointment;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAppointmentStausNotificationToPatient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Appointment $appointment
     */
    public function __construct(private Appointment $appointment)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $patient = $this->appointment->patient;
            $user = $patient->user;
            $patienDefaultLang = $user->default_lang->value;

            $status = __('doctor/notification.appointment-id', ['id' => '#' . Str::padLeft($this->appointment->id, 8, '0')], $patienDefaultLang);
            $doctorFullName = $this->appointment->doctor->user_fullname;

            $notificationData = [
                'record' => $this->appointment->id,
            ];

            switch ($this->appointment->status) {
                case AppointmentStatusEnum::ACCEPTED:
                    $body = $this->appointment->reschedule_date
                        ? __('doctor/notification.appointment-accepted-and-reschedule_date', ['doctor' => $doctorFullName], $patienDefaultLang)
                        : __('doctor/notification.appointment-accepted', ['doctor' => $doctorFullName], $patienDefaultLang);
                    Notification::make()
                        ->title($status)
                        ->body($body)
                        ->icon('heroicon-o-bell')
                        ->success()
                        ->viewData($notificationData)
                        ->sendToDatabase($user);
                    break;
                case AppointmentStatusEnum::FINISHED:
                    $body = __('doctor/notification.appointment-finished', ['doctor' => $doctorFullName], $patienDefaultLang);
                    Notification::make()
                        ->title($status)
                        ->body($body)
                        ->icon('heroicon-o-bell')
                        ->success()
                        ->viewData($notificationData)
                        ->sendToDatabase($user);
                    break;
                default:
                    $body = __('doctor/notification.appointment-refused', ['doctor' => $doctorFullName], $patienDefaultLang);
                    Notification::make()
                        ->title($status)
                        ->body($body)
                        ->icon('heroicon-o-bell')
                        ->danger()
                        ->viewData($notificationData)
                        ->sendToDatabase($user);
            }

            if ($user->one_signal_id) {
                $params = [
                    'small_icon' => 'ic_stat_onesignal_default',
                ];
                $headings = [$status];
                $message = $body;

                OneSignal::addParams($params)->sendNotificationToUser($message, $user->one_signal_id, $headings);
                // switch ($this->appointment->status) {
                //     case AppointmentStatusEnum::ACCEPTED:
                //     case AppointmentStatusEnum::FINISHED:
                //         OneSignal::addParams($params)->sendNotificationToUser($message, $user->one_signal_id, $headings);
                //         break;
                //     case AppointmentStatusEnum::DENIED:
                //         OneSignal::addParams($params)->sendNotificationToUser($message, $user->one_signal_id, $headings);
                //         break;
                // }
            }
        } catch (Exception $e) {
            Log::error('Error SendAppointmentStausNotificationToPatient: ' . $e->getMessage());
        }
    }
}
