<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// No column may be changed through a request.
#[Fillable([])]
class Teacher extends Model
{
    // Sections this teacher advises (sections.adviser_id points here).
    public function advisorySections(): HasMany
    {
        return $this->hasMany(Section::class, 'adviser_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    // "First Last".
    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
