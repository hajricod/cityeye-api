<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasePerson extends Model
{
    use HasFactory;
    protected $table = 'case_persons';

    protected $fillable = [
        'case_id',
        'type',
        'name',
        'age',
        'gender',
        'role'
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }
}
