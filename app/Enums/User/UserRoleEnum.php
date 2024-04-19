<?php

namespace App\Enums\User;

use Kongulov\Traits\InteractWithEnum;

enum UserRoleEnum: string
{
    use InteractWithEnum;

    case ADMIN = 'admin';
    case DOCTOR = 'doctor';
    case PATIENT = 'patient';
}
