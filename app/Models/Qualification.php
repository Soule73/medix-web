<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Model\Qualification
 *
 * @property int $id
 * @property string $name
 * @property string $institute
 * @property string $procurement_date
 * @property int $doctor_id
 * @property string $created_at
 * @property string $updated_at
 *
 */
class Qualification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'institute',
        'procurement_date',
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
            'procurement_date' => 'datetime',
        ];
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,Qualification>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
