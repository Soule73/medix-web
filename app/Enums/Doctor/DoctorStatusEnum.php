<?php

namespace App\Enums\Doctor;

use Kongulov\Traits\InteractWithEnum;

enum DoctorStatusEnum: string
{
    use InteractWithEnum;

    case VALIDATED = 'validated';
    case NOTVALIDATED = 'notvalidated';
}
