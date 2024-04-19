<?php

namespace App\Livewire\Pulse;

use Livewire\Attributes\Url;
use Livewire\Component;

class CustomPeriodSelector extends Component
{
    /**
     * The selected period.
     *
     * @var '1_hour'|'6_hours'|'24_hours'|'7_days'
     */
    #[Url]
    public string $period = '1_hour';

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.pulse.custom-period-selector');
    }
}
