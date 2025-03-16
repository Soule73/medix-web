<?php

namespace App\Models;

use App\Enums\Appointment\AppointmentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Model\Appointment
 *
 * @property int $id
 * @property int $add_by_doctor
 * @property int $doctor_id
 * @property int $patient_id
 * @property int $work_place_id
 * @property string|null $type
 * @property string|null $motif
 * @property string $date_appointment
 * @property string|null $reschedule_date
 * @property string|null $accepted_message
 * @property string|null $reason_for_refusal
 * @property double $amount
 * @property double $discount
 * @property boolean $payed
 * @property boolean $confirm_payed
 * @property boolean $remind_patient
 * @property AppointmentStatusEnum::string $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ReviewRating[] $reviewRatings
 * @property PatientRecord[] $patient_records
 * @property Patient $patient
 * @property Doctor $doctor
 * @property User $user
 * @property City $city
 */
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_appointment' => 'datetime',
            'status' => AppointmentStatusEnum::class,
            'discount' => 'double',
            'amount' => 'double',
            'payed' => 'boolean',
            'remind_patient' => 'boolean',
            'confirm_payed' => 'boolean',
            'add_by_doctor' => 'integer',
        ];
    }

    /**
     * patient
     *
     * @return BelongsTo<Patient,Appointment>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,Appointment>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * reviewRating
     *
     * @return HasOne<ReviewRating>
     */
    public function reviewRating(): HasOne
    {
        return $this->hasOne(ReviewRating::class);
    }

    /**
     * work_place
     *
     * @return BelongsTo<WorkPlace,Appointment>
     */
    public function work_place(): BelongsTo
    {
        return $this->belongsTo(WorkPlace::class);
    }

    /**
     * patient_records
     *
     * @return HasMany<PatientRecord>
     */
    public function patient_records(): HasMany
    {
        return $this->hasMany(PatientRecord::class);
    }
}
