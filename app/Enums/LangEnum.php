<?php

namespace App\Enums;

use Kongulov\Traits\InteractWithEnum;

enum LangEnum: string
{
    use InteractWithEnum;

    case FR = 'fr';
    case EN = 'en';
    case AR = 'ar';
}
