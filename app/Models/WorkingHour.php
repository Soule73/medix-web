<?php

namespace App\Models;

use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => TimeCast::class,
            'end_at' => TimeCast::class,
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function work_place(): BelongsTo
    {
        return $this->belongsTo(WorkPlace::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function getRowCount($hours)
    {
        $startHourIndex = $hours->search(substr($this->start_at, 0, 5));
        $endHourIndex = $hours->search(substr($this->end_at, 0, 5));

        return $endHourIndex - $startHourIndex;
    }
}
