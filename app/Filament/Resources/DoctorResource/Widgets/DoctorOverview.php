<?php

namespace App\Filament\Resources\DoctorResource\Widgets;

use App\Enums\Doctor\DoctorStatusEnum;
use App\Models\Doctor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DoctorOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('doctor/doctor.doctors-label'), Doctor::all()->count())
                ->descriptionIcon('heroicon-s-user-group', 'before')
                ->description(__('doctor/doctor.total'))
                ->color('info'),

            Stat::make(__('doctor/doctor.doctors-label'), Doctor::where('status', DoctorStatusEnum::VALIDATED)->count())
                ->description(__('doctor/doctor.doctor-status-validated'))
                ->descriptionIcon('heroicon-s-check-badge', 'before')
                ->color('success'),

            Stat::make(__('doctor/doctor.doctors-label'), Doctor::where('status', DoctorStatusEnum::NOTVALIDATED)->count())
                ->description(__('doctor/doctor.doctor-status-notvalidated'))
                ->descriptionIcon('heroicon-s-x-circle', 'before')
                ->color('danger'),
        ];
    }
}
