<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Model\ReviewRating
 *
 * @property int $id
 * @property string $comment
 * @property int $star
 * @property int $doctor_id
 * @property int $appointment_id
 * @property int $patient_id
 * @property string $created_at
 * @property string $updated_at
 *
 */
class ReviewRating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'star',
        'comment',
        'appointment_id',
        'doctor_id',
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
            'star' => 'integer',
        ];
    }

    /**
     * appointment
     *
     * @return BelongsTo<Appointment,ReviewRating>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,ReviewRating>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * patient
     *
     * @return BelongsTo<Patient,ReviewRating>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
