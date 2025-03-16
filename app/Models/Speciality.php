<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Model\Speciality
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 *
 */
class Speciality extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name ',
        'description ',
    ];

    /**
     * doctors
     *
     * @return BelongsToMany<Doctor,Speciality>
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_speciality');
    }

    /**
     * doctor_speciality
     *
     * @return HasMany
     */
    public function doctor_speciality(): HasMany
    {
        return $this->hasMany(DoctorSpeciality::class);
    }
}
