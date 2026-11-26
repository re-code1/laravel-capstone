<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

// Who may change one grade: the admin, or the teacher who gave it.
class GradePolicy
{
    public function update(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->teacher_id !== null && $user->teacher_id === $grade->teacher_id;
    }
}
