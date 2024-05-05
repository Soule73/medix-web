<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Widgets;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class AppointmentsPerMonthBarChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '10s';

    protected static ?string $maxHeight = '300px';

    // protected static ?string $heading = "Bilan de l'année";
    public function getHeading(): string
    {
        return __('doctor/chart.appointment-per-month-title');
    }

    public function getDescription(): string
    {
        return __('doctor/chart.appointment-per-month-description', ['year' => now()->format('Y')]);
    }

    protected function getData(): array
    {
        $local = session()->get('locale') ?? config('app.locale', 'fr');
        $doctorId = auth()->user()->doctor->id;
        Carbon::setLocale($local);
        $period = CarbonPeriod::create('first day of January', 'last day of December');
        $months = $period->month()->toArray();
        $doctorId = auth()->user()->doctor->id;

        $appointmentsData = collect($months)->mapWithKeys(function ($month) use ($doctorId) {
            $monthName = $month->translatedFormat('F');
            $acceptedOrFinishied = Appointment::whereIn('status', [
                AppointmentStatusEnum::ACCEPTED->value,
                AppointmentStatusEnum::FINISHED->value
            ])
                ->where('doctor_id', $doctorId)
                ->whereMonth('created_at', $month->month)
                ->count();

            $refused = Appointment::where('status', AppointmentStatusEnum::DENIED->value)
                ->where('doctor_id', $doctorId)
                ->whereMonth('created_at', $month->month)
                ->count();

            return [
                $monthName => [
                    'acceptedOrFinishied' => $acceptedOrFinishied,
                    'refused' => $refused,
                ],
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('doctor/appointment.accepted') . '/' . __('doctor/appointment.finished'),
                    'data' => $appointmentsData->pluck('acceptedOrFinishied')->values(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                    'borderRadius' => 10,
                    'maxBarThickness' => 25,
                ],
                [
                    'label' => __('doctor/appointment.refused'),
                    'data' => $appointmentsData->pluck('refused')->values(),
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
                    'borderRadius' => 10,
                    'maxBarThickness' => 25,
                ],

            ],
            'labels' => $appointmentsData->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
