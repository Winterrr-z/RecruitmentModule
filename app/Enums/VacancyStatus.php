<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case DRAFT = 'Draft';
    case PUBLISHED = 'Published';
    case COMPLETED = 'Completed';
    case CLOSED = 'Closed';
}
