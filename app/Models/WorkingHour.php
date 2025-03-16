<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Model\WorkingHour
 *
 * @property int $id
 * @property string $start_at
 * @property string $end_at
 * @property int $day_id
 * @property int $work_place_id
 * @property string $created_at
 * @property string $updated_at
 *
 */
class WorkingHour extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'start_at',
        'end_at',
        'day_id',
        'work_place_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => TimeCast::class,
            'end_at' => TimeCast::class,
        ];
    }

    /**
     * doctor
     *
     * @return BelongsTo<Doctor,WorkingHour>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * work_place
     *
     * @return BelongsTo<WorkPlace,WorkingHour>
     */
    public function work_place(): BelongsTo
    {
        return $this->belongsTo(WorkPlace::class);
    }

    /**
     * day
     *
     * @return BelongsTo<Day,WorkingHour>
     */
    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    /**
     * getRowCount
     *
     * @param  mixed $hours
     * @return float
     */
    public function getRowCount($hours): float
    {
        $startHourIndex = $hours->search(substr($this->start_at, 0, 5));
        $endHourIndex = $hours->search(substr($this->end_at, 0, 5));

        return $endHourIndex - $startHourIndex;
    }
}
