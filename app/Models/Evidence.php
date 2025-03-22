<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    protected $table = 'evidences';

    public function case()
    {
        return $this->belongsTo(Cases::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
