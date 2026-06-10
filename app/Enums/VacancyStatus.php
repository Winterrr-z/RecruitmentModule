<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case DRAFT = 'Draft';
    case READY_TO_PUBLISH = 'Ready to Publish';
    case PUBLISHED = 'Published';
    case CLOSED = 'Closed';
    case COMPLETED_CLOSED = 'Completed/Closed';
}
