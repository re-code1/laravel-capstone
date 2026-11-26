<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

// Who may see one section: the admin, or the teacher who advises it.
// Keep in step with Section::scopeVisibleTo(), which applies the same rule to a list.
class SectionPolicy
{
    public function view(User $user, Section $section): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->teacher_id !== null && $user->teacher_id === $section->adviser_id;
    }
}
