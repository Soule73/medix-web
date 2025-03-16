<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Model\Patient
 *
 * @property int $id
 * @property string $id_cnss
 * @property string $addresse
 * @property string $birthday
 * @property int $user_id
 * @property int $city_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ReviewRating $reviewRatings
 * @property PatientRecord $patient_records
 * @property Appointment $appointments
 * @property User $user
 * @property City $city
 *
 *
 */
class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cnss',
        'addresse',
        'birthday',
        'user_id',
        'city_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'datetime',
        ];
    }

    /**
     * user
     *
     * @return BelongsTo<User,Patient>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * city
     *
     * @return BelongsTo<City,Patient>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * appointments
     *
     * @return HasMany
     */
    /**
     * appointments
     *
     * @return HasMany<Appointment>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * reviewRatings
     *
     * @return HasMany<ReviewRating>
     */
    public function reviewRatings(): HasMany
    {
        return $this->hasMany(ReviewRating::class);
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

    /**
     * getUserFullnameAttribute
     *
     * @return string|null
     */
    public function getUserFullnameAttribute(): string|null
    {
        return $this->user->fullname;
    }
}
