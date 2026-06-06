<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case APPLIED = 'Applied';
    case IN_PROGRESS = 'In Progress';
    case OFFERED = 'Offered';
    case HIRED = 'Hired';
    case DECLINED = 'Declined';
    case EXPIRED = 'Expired';
    case REJECTED = 'Rejected';
    case WITHDRAWN = 'Withdrawn';
    case BLACKLISTED = 'Blacklisted';
}
