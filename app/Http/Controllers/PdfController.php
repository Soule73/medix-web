<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\PatientRecord;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function viewPrescription(Request $request, string $prescription_id)
    {
        $local = session()->get('locale')
            ?? $request->get('locale')
            ?? $request->cookie('filament_language_switch_locale')
            ?? config('app.locale', 'fr');
        $prescription = PatientRecord::findOrFail($prescription_id);
        $doctorId = auth()->user()->doctor->id;
        $doctor = Doctor::whereHas('appointments', function ($query) use ($doctorId) {
            return $query->where('doctor_id', $doctorId);
        })->findOrFail($doctorId);
        return view('filament.doctor.components.prescription', [
            'prescription' => $prescription,
            'local' => $local,
        ]);
    }
}
