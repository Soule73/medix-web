<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Model\City
 *
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 *
 */
class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * workPlaces
     *
     * @return HasMany<WorkPlace>
     */
    public function workPlaces(): HasMany
    {
        return $this->hasMany(WorkPlace::class);
    }

    /**
     * patients
     *
     * @return HasMany<Patient>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
