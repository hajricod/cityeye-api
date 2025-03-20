<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin        = 'admin';
    case Investigator = 'investigator';
    case Officer      = 'officer';
    case Citizen      = 'citizen';
    case Auditor      = 'auditor';
}
