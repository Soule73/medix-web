<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * App\Model\WorkingHour
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property double $longitude
 * @property double $latitude
 * @property int $city_id
 * @property int $doctor_id
 * @property string $created_at
 * @property string $updated_at
 * @property Doctor $doctor
 * @property Appointment[] $appointments
 * @property City $City
 * @property WorkingHour[] $workingHours
 *
 */
class WorkPlace extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'city_id',
        'doctor_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'double',
            'longitude' => 'double',
        ];
    }

    /**
     * appointments
     *
     * @return HasMany<HasMany>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * workingHours
     *
     * @return HasMany<WorkingHour>
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,Workplace>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * city
     *
     * @return BelongsTo<City,Workplace>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
