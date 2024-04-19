<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AppointmentLine extends ChartWidget
{
    protected static ?string $pollingInterval = '10s';

    public ?string $filter = 'week';
    // protected static ?string $heading = 'Appointments by Day';

    public function getHeading(): string
    {
        return __('doctor/appointment.appointment-per-day');
    }

    public function getAppointmentsCountByDay($activeFilter, $doctorId)
    {
        $period = [
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
        ];

        $start = $period[$activeFilter][0] ?? $period['month'][0];
        $end = $period[$activeFilter][1] ?? $period['month'][1];

        return Trend::query(Appointment::where('doctor_id', $doctorId))
            ->between(start: $start, end: $end)
            ->perDay()
            ->count();
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $local = session()->get('locale') ?? config('app.locale', 'fr');
        $doctorId = auth()->user()->doctor->id;
        Carbon::setLocale($local);
        // l : Représentation textuelle complète du jour de la semaine.
        // j : Jour du mois sans les zéros initiaux.
        // F : Représentation textuelle complète du mois.
        // Y : Représentation numérique complète de l’année sur quatre chiffres.
        // H : Format 24 heures de l’heure avec les zéros initiaux.
        // i : Minutes avec les zéros initiaux.
        // s : Secondes avec les zéros initiaux.
        // dd(Carbon::parse(now())->translatedFormat('l j F Y H:i:s'));
        // "السبت 16 مارس 2024 12:48:03"

        $appointmentsCountByDay = $this->getAppointmentsCountByDay($activeFilter, $doctorId);

        return [
            'datasets' => [
                [
                    'label' => __('doctor/appointment.model-label-plural'),
                    'data' => $appointmentsCountByDay->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
                //
            ],
            'labels' => $appointmentsCountByDay->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat($local === 'en' ? 'F j' : 'j F')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => __('doctor/appointment.week'),
            'month' => __('doctor/appointment.month'),
            'year' => __('doctor/appointment.year'),
        ];
    }
}
