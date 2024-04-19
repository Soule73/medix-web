<?php

namespace App\Enums\DocumentsForValidation;

use Kongulov\Traits\InteractWithEnum;

enum DocumentsForValidationStatusEnum: string
{
    use InteractWithEnum;

    case VALIDATED = 'validated';
    case NOTVALIDATED = 'notvalidated';
    case Pending = 'pending';
}
