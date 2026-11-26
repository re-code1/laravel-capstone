<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

// Who may change a grade? The registrar, or the teacher who gave it.
class GradePolicy
{
    public function update(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->teacher_id !== null && $grade->teacher_id === $user->teacher_id;
    }
}
