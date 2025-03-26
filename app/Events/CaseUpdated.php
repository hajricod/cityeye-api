<?php

namespace App\Events;

use App\Models\Cases;
use Illuminate\Foundation\Events\Dispatchable;

class CaseUpdated
{
    use Dispatchable;

    public function __construct(public Cases $case) {}
}

