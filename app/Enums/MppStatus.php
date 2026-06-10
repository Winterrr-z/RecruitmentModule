<?php

namespace App\Enums;

enum MppStatus: string
{
    case DRAFT = 'Draft';
    case APPROVED = 'Approved';
    case COMPLETED = 'Completed';
    case CLOSED = 'Closed';
}
