<?php

namespace App\Models;

use App\Enums\CaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'case_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'case_assignees', 'case_id', 'user_id')
                    ->withPivot('assigned_role')
                    ->withTimestamps();
    }

    public function persons()
    {
        return $this->hasMany(CasePerson::class, 'case_id');
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class, 'case_id');
    }

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
