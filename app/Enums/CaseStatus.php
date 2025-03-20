<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Pending    = 'pending';
    case Ongoing    = 'ongoing';
    case Closed     = 'closed';

}
