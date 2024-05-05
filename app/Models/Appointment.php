<?php

namespace App\Models;

use App\Enums\Appointment\AppointmentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'status',
        'date_appointment',
        'reschedule_date',
        'motif',
        'reason_for_refusal',
        'accepted_message',
        'payed',
        'work_place_id',
        'doctor_id',
        'patient_id',
        'add_by_doctor',
        'amount',
        'discount',
        'confirm_payed',
        'remind_patient',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_appointment' => 'datetime',
            'status' => AppointmentStatusEnum::class,
            'payed' => 'boolean',
            'discount' => 'float',
            'amount' => 'float',
            'remind_patient' => 'boolean',
            'add_by_doctor' => 'boolean',
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

    public function reviewRating(): HasOne
    {
        return $this->hasOne(ReviewRating::class);
    }

    public function work_place(): BelongsTo
    {
        return $this->belongsTo(WorkPlace::class);
    }

    public function patient_records(): HasMany
    {
        return $this->hasMany(PatientRecord::class);
    }
}
