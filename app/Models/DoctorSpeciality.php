<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Model\DoctorSpeciality
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $speciality_id
 * @property string $created_at
 * @property string $updated_at
 *
 */
class DoctorSpeciality extends Model
{
    protected $table = 'doctor_speciality';

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',
        'speciality_id',
    ];

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,DoctorSpeciality>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * speciality
     *
     * @return BelongsTo<Speciality,DoctorSpeciality>
     */
    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }
}
