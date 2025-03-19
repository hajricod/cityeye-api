<?php

namespace App\Enums;

enum UserRole: string
{
    case Low       = 'low';
    case Medium    = 'medium';
    case High      = 'high';
    case Critical  = 'critical';

}
