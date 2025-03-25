<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseAssignees extends Model
{
    use HasFactory;

    protected $table = 'case_assignees';

    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
