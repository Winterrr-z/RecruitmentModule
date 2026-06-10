<?php

namespace App\Enums;

enum MppProgressStatus: string
{
    case IN_PROGRESS = 'In Progress';
    case NEED_ATTENTION = 'Need Attention';
    case URGENT = 'Urgent';
    case CRITICAL = 'Critical';
}
