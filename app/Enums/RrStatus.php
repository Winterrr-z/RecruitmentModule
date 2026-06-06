<?php

namespace App\Enums;

enum RrStatus: string
{
    case DRAFT = 'Draft';
    case READY_TO_PUBLISH = 'Ready to Publish';
    case PUBLISHED = 'Published';
    case COMPLETED_CLOSED = 'Completed/Closed';
    case COMPLETED = 'Completed';
    case CLOSED = 'Closed';
}
