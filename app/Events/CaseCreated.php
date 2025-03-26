<?php

namespace App\Events;

use App\Models\Cases;
use Illuminate\Foundation\Events\Dispatchable;

class CaseCreated
{
    use Dispatchable;

    public function __construct(public Cases $case) {}
}

