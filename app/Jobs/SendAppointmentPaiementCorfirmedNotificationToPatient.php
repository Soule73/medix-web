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

class SendAppointmentPaiementCorfirmedNotificationToPatient implements ShouldQueue
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
            $default_lang = $patient->default_lang->value;
            $oneSignald = $patient->one_signal_id;
            $id = "#" . Str::padLeft($this->appointment->id, 8, '0');
            $status = __('doctor/notification.appointment-id', ['id' => $id], $default_lang  ?? config('app.locale'));
            $content = __('doctor/notification.payement-confirmed', ['amount' => $this->appointment->amount], $default_lang ?? config('app.locale'));
            if ($this->appointment->payed) {
                Notification::make()
                    ->title($status)
                    ->body($content)
                    ->success()
                    ->viewData(["record" => $this->appointment->id])
                    ->sendToDatabase($this->appointment->patient->user);

                if ($oneSignald) {
                    $params = [];
                    $params['small_icon'] = 'ic_stat_onesignal_default';
                    OneSignalFacade::addParams($params)->sendNotificationToUser(
                        message: $content,
                        userId: $oneSignald,
                        headings: $status,
                    );

                    $this->appointment->confirm_payed = true;
                    $this->appointment->save();
                }
            }
        } catch (Exception $e) {
            Log::error("Error SendAppointmentPaiementCorfirmedNotificationToPatient: " . $e->getMessage());
        }
    }
}
