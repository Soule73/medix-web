<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Widgets;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentBar extends ChartWidget
{
    protected static ?string $pollingInterval = '10s';

    public ?string $filter = 'week';

    // protected static ?string $heading = 'Appointments Status';

    public function getHeading(): string
    {
        return __('doctor/appointment.appointment-satus');
    }
    // protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $doctorId = auth()->user()->doctor->id;

        $periods = [
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
        ];

        $startDate = $periods[$activeFilter][0];
        $endDate = $periods[$activeFilter][1];

        $accepted = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', AppointmentStatusEnum::ACCEPTED->value)
            ->count();

        $refused = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', AppointmentStatusEnum::DENIED->value)
            ->count();

        $pending = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', AppointmentStatusEnum::PENDING->value)
            ->count();
        $finished = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', AppointmentStatusEnum::FINISHED->value)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => __('doctor/doctor.doctor-status'),
                    'data' => [
                        $finished,
                        $accepted,
                        $pending,
                        $refused,
                    ],
                    'backgroundColor' => [
                        '#36A2EB',
                        '#4CAF50',
                        '#FFC107',
                        '#F44336',
                    ],
                    'borderRadius' => 10,
                    'borderWidth' => 0,
                    'maxBarThickness' => 25,

                ],
            ],
            'labels' => [
                __('doctor/appointment.finished'),
                __('doctor/appointment.accepted'),
                __('doctor/appointment.pending'),
                __('doctor/appointment.refused'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => __('doctor/appointment.today'),
            'week' => __('doctor/appointment.week'),
            'month' => __('doctor/appointment.month'),
            'year' => __('doctor/appointment.year'),
        ];
    }
}
