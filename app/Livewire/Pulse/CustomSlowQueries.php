<?php

namespace App\Livewire\Pulse;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Recorders\SlowQueries as SlowQueriesRecorder;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;

/**
 * @internal
 */
#[Lazy]
class CustomSlowQueries extends Card
{
    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-queries')]
    public string $orderBy = 'slowest';

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$slowQueries, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_query',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$sql, $location] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'sql' => $sql,
                    'location' => $location,
                    'slowest' => $row->max,
                    'count' => $row->count,
                ];
            }),
            $this->orderBy,
        );

        return view('livewire.pulse.custom-slow-queries', [
            'time' => $time,
            'runAt' => $runAt,
            'config' => [
                'highlighting' => true,
                ...Config::get('pulse.recorders.'.SlowQueriesRecorder::class),
            ],
            'slowQueries' => $slowQueries,
        ]);
    }
}
