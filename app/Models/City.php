<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function workPlaces(): HasMany
    {
        return $this->hasMany(WorkPlace::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
