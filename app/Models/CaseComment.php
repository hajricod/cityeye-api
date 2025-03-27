<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseComment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }
}
