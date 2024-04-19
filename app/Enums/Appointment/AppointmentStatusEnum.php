<?php

namespace App\Enums\Appointment;

use Kongulov\Traits\InteractWithEnum;

enum AppointmentStatusEnum: string
{
    use InteractWithEnum;

    case ACCEPTED = 'accepted';
    case PENDING = 'pending';
    case DENIED = 'denied';
    case FINISHED = 'finished';
}
