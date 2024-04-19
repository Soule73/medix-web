<?php

namespace App\Livewire\Pulse;

use Livewire\Attributes\Url;
use Livewire\Attributes\Lazy;
use Laravel\Pulse\Livewire\Card;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Support\Renderable;
use Laravel\Pulse\Recorders\SlowRequests as SlowRequestsRecorder;

/**
 * @internal
 */
#[Lazy]
class CustomSlowRequests extends Card
{
    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-requests')]
    public string $orderBy = 'slowest';

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$slowRequests, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_request',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$method, $uri, $action] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'uri' => $uri,
                    'method' => $method,
                    'action' => $action,
                    'count' => $row->count,
                    'slowest' => $row->max,
                ];
            }),
            $this->orderBy,
        );

        return view('livewire.pulse.custom-slow-requests', [
            'time' => $time,
            'runAt' => $runAt,
            'slowRequests' => $slowRequests,
            'config' => [
                'threshold' => Config::get('pulse.recorders.' . SlowRequestsRecorder::class . '.threshold'),
                'sample_rate' => Config::get('pulse.recorders.' . SlowRequestsRecorder::class . '.sample_rate'),
            ],
        ]);
    }
}
