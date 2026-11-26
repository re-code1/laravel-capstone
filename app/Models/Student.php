<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// No column may be changed through a request.
#[Fillable([])]
class Student extends Model
{
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    // "Last, First", the same format as Day 2.
    public function fullName(): string
    {
        return "{$this->last_name}, {$this->first_name}";
    }
}
