<?php

namespace App\Livewire\Pulse;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Laravel\Pulse\Livewire\Card;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Support\Renderable;
use Laravel\Pulse\Recorders\Queues as QueuesRecorder;

/**
 * @internal
 */
#[Lazy]
class CustomQueues extends Card
{
    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$queues, $time, $runAt] = $this->remember(fn () => $this->graph(
            ['queued', 'processing', 'processed', 'released', 'failed'],
            'count',
        ));

        if (Livewire::isLivewireRequest()) {
            $this->dispatch('queues-chart-update', queues: $queues);
        }

        return view('livewire.pulse.custom-queues', [
            'queues' => $queues,
            'showConnection' => $queues->keys()->map(fn ($queue) => Str::before($queue, ':'))->unique()->count() > 1,
            'time' => $time,
            'runAt' => $runAt,
            'config' => Config::get('pulse.recorders.' . QueuesRecorder::class),
        ]);
    }
}
