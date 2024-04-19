<?php

namespace App\Enums\Doctor;

use Kongulov\Traits\InteractWithEnum;

enum DocumentForValidationEnum: string
{
    use InteractWithEnum;

    case FRONT_IDENTITY_CARD = 'front_identity_card';
    case IDENTITY_CARD_BACK = 'identity_card_back';
    case PASSPORT = 'passport';
    case CERTIFICATE_OF_REGISTRATION = 'certificate_of_registration';
}
