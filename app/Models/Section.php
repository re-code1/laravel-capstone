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

    // The sections a user may see, as a query. Same rule as SectionPolicy::view(). Change both together.
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        // where('adviser_id', null) would match sections with no adviser, so a login with no teacher sees nothing.
        $user->teacher_id === null
            ? $query->whereRaw('1 = 0')
            : $query->where('adviser_id', $user->teacher_id);
    }
}
