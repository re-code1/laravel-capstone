<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

// Who may see a section (and so its students and report cards)?
// The registrar sees all; a teacher sees only the section they advise.
// The same rule is written as a query in Section::scopeVisibleTo(). Change both together.
class SectionPolicy
{
    public function view(User $user, Section $section): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // A section with no adviser must not match a login with no teacher (null === null).
        return $user->teacher_id !== null && $section->adviser_id === $user->teacher_id;
    }
}
