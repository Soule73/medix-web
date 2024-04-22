<?php

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Jobs\RemindPatientForAppointment;
use App\Models\Appointment;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new RemindPatientForAppointment(
    appointments: Appointment::where('status', AppointmentStatusEnum::ACCEPTED->value)
        ->where('remind_patient', false)
        ->where('date_appointment', '>=', now())
        ->where('date_appointment', '<=', now()->addMinutes(30))
        ->get()
))->everyMinute();
