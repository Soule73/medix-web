<?php

namespace App\Enums\User;

use Kongulov\Traits\InteractWithEnum;

enum UserSexEnum: string
{
    use InteractWithEnum;

    case MAN = 'M';
    case WOMAN = 'W';
}
