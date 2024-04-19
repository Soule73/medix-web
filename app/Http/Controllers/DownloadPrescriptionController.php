<?php

namespace App\Http\Controllers;

use App\Models\PatientRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DownloadPrescriptionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $prescription_id)
    {
        $local = session()->get('locale')
            ?? $request->get('locale')
            ?? $request->cookie('filament_language_switch_locale')
            ?? config('app.locale', 'fr');
        $prescription = PatientRecord::findOrFail($prescription_id);
        $pdf_name = Str::slug('prescription'.now()).'.pdf';

        // dd($local, $prescription);
        $data = [
            'prescription' => $prescription,
            'local' => $local,
        ];

        $pdf = Pdf::loadView(
            'filament.doctor.components.prescription-print',
            $data
        );

        // landscape | portrait
        return $pdf->setPaper('a4')
            ->stream($pdf_name);
    }
}
