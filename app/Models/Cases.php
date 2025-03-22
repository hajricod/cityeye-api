<?php

namespace App\Models;

use App\Enums\CaseType;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'case_type' => CaseType::class,
        ];
    }
}
