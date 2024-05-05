<?php

namespace App\Jobs;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendConfirmValidationIdentityToDoctr;

class SendConfirmValidationIdentityToDoctrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Doctor $doctor)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $doctorDefaultLang = $this->doctor->default_lang->value  ?? config('app.locale');
        $title = __('doctor/doctor.approved-notification-title', [], $doctorDefaultLang);
        $body = __('doctor/doctor.approved-notification-body', [], $doctorDefaultLang);
        $doctorFullName = $this->doctor->professional_title . " " . $this->doctor->user_fullname;
        $doctorEmail = $this->doctor->user->email;

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-s-check-badge')
            ->success()
            ->sendToDatabase($this->doctor->user);

        Mail::to($doctorEmail)->send(new SendConfirmValidationIdentityToDoctr(doctorFullName: $doctorFullName, title: $title, body: $body));
    }
}
