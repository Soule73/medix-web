<?php

namespace App\Livewire\Pulse;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Laravel\Pulse\Recorders\CacheInteractions as CacheInteractionsRecorder;
use Livewire\Attributes\Lazy;

/**
 * @internal
 */
#[Lazy]
class CustomCache extends Card
{
    use Concerns\HasPeriod, Concerns\RemembersQueries;

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$cacheInteractions, $allTime, $allRunAt] = $this->remember(
            fn () => with(
                $this->aggregateTotal(['cache_hit', 'cache_miss'], 'count'),
                fn ($results) => (object) [
                    'hits' => $results['cache_hit'] ?? 0,
                    'misses' => $results['cache_miss'] ?? 0,
                ]
            ),
            'all'
        );

        [$cacheKeyInteractions, $keyTime, $keyRunAt] = $this->remember(
            fn () => $this->aggregateTypes(['cache_hit', 'cache_miss'], 'count')
                ->map(function ($row) {
                    return (object) [
                        'key' => $row->key,
                        'hits' => $row->cache_hit ?? 0,
                        'misses' => $row->cache_miss ?? 0,
                    ];
                }),
            'keys'
        );

        return view('livewire.pulse.custom-cache', [
            'allTime' => $allTime,
            'allRunAt' => $allRunAt,
            'allCacheInteractions' => $cacheInteractions,
            'keyTime' => $keyTime,
            'keyRunAt' => $keyRunAt,
            'cacheKeyInteractions' => $cacheKeyInteractions,
            'config' => Config::get('pulse.recorders.'.CacheInteractionsRecorder::class),
        ]);
    }
}
