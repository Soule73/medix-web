<?php

namespace App\Filament\Doctor\Resources\DoctorResource\Widgets;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DoctorOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $doctorId = auth()->user()->doctor->id;

        return [
            Stat::make(
                __('doctor/chart.doctor-total-patient'),
                Patient::whereHas('appointments', function ($query) use ($doctorId) {
                    return $query->where('doctor_id', $doctorId)
                        ->where('status', AppointmentStatusEnum::FINISHED->value)
                        ->orWhere('status', AppointmentStatusEnum::ACCEPTED->value)
                        ->where('date_appointment', '<=', now());
                })->count()
            )
                ->extraAttributes(['class' => 'doctor-total-patient'])
                ->icon('heroicon-s-user-group'),
            Stat::make(__('doctor/chart.doctor-total-appointment'), Appointment::where('doctor_id', $doctorId)->count())
                ->icon('heroicon-s-calendar-days')
                ->extraAttributes(['class' => 'doctor-total-appointment']),
            Stat::make(
                __('doctor/chart.doctor-total-income'),
                Appointment::where('doctor_id', $doctorId)
                    ->where('payed', '=', true)
                    ->sum('amount')
            )

                ->extraAttributes(['class' => 'doctor-total-income'])
                ->icon('heroicon-s-currency-euro'),
        ];
    }
}
