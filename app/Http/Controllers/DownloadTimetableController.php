<?php

namespace App\Http\Controllers;

use App\Models\Day;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;

class DownloadTimetableController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $local = session()->get('locale')
            ?? $request->get('locale')
            ?? $request->cookie('filament_language_switch_locale')
            ?? config('app.locale', 'fr');
        $doctor_id = auth()->user()->doctor->id;
        $user = auth()->user();

        $workingHours = Day::with(
            [
                'working_hours' => fn ($query) => $query->where('doctor_id', $doctor_id)
                    ->orderBy('start_at')
                    ->with('work_place'),
            ]
        )
            ->orderBy('id')
            ->get();
        $hours = collect([
            '08:00', '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00', '17:00',
            '18:00', '19:00', '20:00', '21:00', '22:00',
            '23:00',
        ]);
        $days = Day::with(
            [
                'working_hours' => function ($query) use ($doctor_id) {
                    $query->where('doctor_id', $doctor_id)->orderBy('start_at')->with('work_place');
                },
            ]
        )->orderBy('id')->get();

        $data = [
            'user' => $user,
            'workingHours' => $workingHours,
            'hours' => $hours,
            'days' => $days,
            'local' => $local,
        ];
        $view = 'filament.doctor.components.schedule';
        $pdf_name = Str::slug($user->first_name.'-'.$user->name.'-'.__('doctor/relation/working-hour.timetable', [], $local)).'.pdf';

        if ($local === 'ar') {
            $pdf = LaravelMpdf::loadView($view, $data, [], ['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);

            return $pdf->stream($pdf_name);
        } else {
            $pdf = Pdf::loadView(
                $view,
                $data
            );

            // landscape | portrait
            return $pdf->setPaper('a4', 'landscape')
                ->stream($pdf_name);
        }
    }
}
