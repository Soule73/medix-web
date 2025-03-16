<?php

namespace App\Jobs;

use App\Models\Appointment;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewAppointmentFollowUpNotificationToPatien implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Appointment $appointment)
    {
        $patient = $this->appointment->patient;
        $user = $patient->user;
        $patienDefaultLang = $user->default_lang->value;

        $doctorFullName = $this->appointment->doctor->user_fullname;
        $status = __('doctor/notification.follow-up-appointment-title', [], $patienDefaultLang);
        $body = __('doctor/notification.follow-up-appointment-body', ['doctor' => $doctorFullName], $patienDefaultLang);

        $notificationData = [
            'record' => $this->appointment->id,
        ];

        Notification::make()
            ->title($status)
            ->body($body)
            ->icon('heroicon-o-bell')
            ->success()
            ->viewData($notificationData)
            ->sendToDatabase($user);

        if ($user->one_signal_id) {
            $params = [
                'small_icon' => 'ic_stat_onesignal_default',
            ];
            $headings = [$status];
            $message = $body;

            OneSignal::addParams($params)->sendNotificationToUser($message, $user->one_signal_id, $headings);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
