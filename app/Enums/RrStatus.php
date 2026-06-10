<?php

namespace App\Enums;

enum RrStatus: string
{
    case READY_TO_PUBLISH = 'Ready to Publish';
    case PUBLISHED = 'Published';
    case COMPLETED = 'Completed';
    case CLOSED = 'Closed';
}
