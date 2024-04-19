<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Widgets;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $doctorId = auth()->user()->doctor->id;

        // Utiliser Eager Loading si nécessaire ici

        // Récupérer les comptes pour chaque statut en une seule requête
        $appointmentsCount = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereIn('status', [
                AppointmentStatusEnum::ACCEPTED,
                AppointmentStatusEnum::PENDING,
                AppointmentStatusEnum::DENIED,
                AppointmentStatusEnum::FINISHED,
            ])
            ->get()
            ->groupBy('status')
            ->map->count();

        return [
            Stat::make(__('doctor/appointment.action-accepted'), $appointmentsCount[AppointmentStatusEnum::ACCEPTED->value] ?? 0)
                ->description(__('doctor/appointment.model-label'))
                ->descriptionIcon('heroicon-s-check-circle', 'before')
                ->color('success')
                ->extraAttributes([]),
            Stat::make(__('doctor/appointment.finished'), $appointmentsCount[AppointmentStatusEnum::FINISHED->value] ?? 0)
                ->description(__('doctor/appointment.model-label'))
                ->descriptionIcon('heroicon-s-document-check', 'before')
                ->color('info'),
            Stat::make(__('doctor/appointment.pending'), $appointmentsCount[AppointmentStatusEnum::PENDING->value] ?? 0)
                ->description(__('doctor/appointment.model-label'))
                ->descriptionIcon('heroicon-s-receipt-refund', 'before')
                ->color('warning'),
            Stat::make(__('doctor/appointment.action-refused'), $appointmentsCount[AppointmentStatusEnum::DENIED->value] ?? 0)
                ->description(__('doctor/appointment.model-label'))
                ->descriptionIcon('heroicon-s-x-circle', 'before')
                ->color('danger'),
        ];
    }
}
