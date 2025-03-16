<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Model\PatientRecord
 *
 * @property int $id
 * @property string $diagnostic
 * @property string $observation
 * @property string $prescription
 * @property string $path
 * @property string $type
 * @property int $doctor_id
 * @property int $appointment_id
 * @property int $patient_id
 * @property string $created_at
 * @property string $updated_at
 *
 */
class PatientRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'observation',
        'prescription',
        'diagnostic',
        'path',
        'type',
        'doctor_id',
        'appointment_id',
        'patient_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'prescription' => 'json',
        ];
    }

    /**
     * patient
     *
     * @return BelongsTo<Patient,PatientRecord>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,PatientRecord>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * appointment
     *
     * @return BelongsTo<Appointment,PatientRecord>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * getPreinscriptionDate
     *
     * @param  DateTime $dateTime
     * @return string
     */
    public function getPreinscriptionDate(DateTime $dateTime): string
    {
        $date = $dateTime;
        $local = session()->get('locale')
            ?? config('app.locale', 'fr');
        Carbon::setLocale($local);

        if ($local === 'en') {
            Carbon::parse($date)->translatedFormat('l j F Y');
        }

        return Carbon::parse($date)->translatedFormat('l j F Y');
    }
}
