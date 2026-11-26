<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    // The section's adviser is a Teacher; the foreign key is adviser_id.
    public function adviser(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'adviser_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // Sections this user may see: all for the admin, only advised ones for a teacher.
    // Keep in step with SectionPolicy::view(), which applies the same rule to one section.
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        // A user with no teacher_id matches nothing (never everything).
        return $user->teacher_id === null
            ? $query->whereRaw('1 = 0')
            : $query->where('adviser_id', $user->teacher_id);
    }
}
