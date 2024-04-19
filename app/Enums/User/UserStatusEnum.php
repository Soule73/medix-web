<?php

namespace App\Enums\User;

use Kongulov\Traits\InteractWithEnum;

enum UserStatusEnum: string
{
    use InteractWithEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
