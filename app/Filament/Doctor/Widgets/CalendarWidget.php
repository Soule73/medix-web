<?php

namespace App\Filament\Doctor\Widgets;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    // protected static string $view = 'filament.doctor.widgets.calendar-widget';
    public Model|string|null $model = Appointment::class;

    public function fetchEvents(array $fetchInfo): array
    {
        $doctorId = auth()->user()->doctor->id;

        return Appointment::where('doctor_id', $doctorId)
            ->where('status', AppointmentStatusEnum::ACCEPTED)
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => 'RDV avec : ' . $appointment->patient->user_fullname,
                    'start' => $appointment->date_appointment,
                    'url' => AppointmentResource::getUrl(name: 'view', parameters: ['record' => $appointment]),
                ];
            })
            ->toArray();
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(false),
        ];
    }

    public static function canView(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
