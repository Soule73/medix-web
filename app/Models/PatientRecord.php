<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'doctor_id', 'path', 'type',

        'appointment_id', 'patient_id', 'city_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {

        return [
            'prescription' => 'json',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getPreinscriptionDate(DateTime $dateTime)
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
