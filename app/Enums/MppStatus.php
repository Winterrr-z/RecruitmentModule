<?php

namespace App\Enums;

enum MppStatus: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending Approval';
    case APPROVED = 'Approved';
    case IN_PROGRESS = 'In Progress';
    case NEED_ATTENTION = 'Need Attention';
    case URGENT = 'Urgent';
    case CRITICAL = 'Critical';
    case FILLED = 'Filled';
    case CLOSED = 'Closed';
    case COMPLETED_CLOSED = 'Completed/Closed';
}
