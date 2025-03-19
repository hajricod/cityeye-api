<?php

namespace App\Enums;

enum AuthorizationLevel: string
{
    case Low       = 'low';
    case Medium    = 'medium';
    case High      = 'high';
    case Critical  = 'critical';

}
