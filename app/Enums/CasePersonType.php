<?php

namespace App\Enums;

enum CasePersonType: string
{
    case Suspect    = 'suspect';
    case Victim     = 'victim';
    case Witness    = 'witness';

}
