<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use App\Models\User;

final class EnrollmentNotePolicy
{
    public function viewAny(User $auth, Enrollment $enrollment): bool
    {
        return $auth->role === UserRole::Admin || $auth->role === UserRole::Coach;
    }

    public function create(User $auth, Enrollment $enrollment): bool
    {
        if ($auth->role === UserRole::Admin) {
            return true;
        }

        if ($auth->role !== UserRole::Coach) {
            return false;
        }

        return $this->isAssignedCoach($auth, $enrollment);
    }

    public function delete(User $auth, EnrollmentNote $note): bool
    {
        return $auth->role === UserRole::Admin
            || $auth->id === $note->author_id;
    }

    public function update(User $auth, EnrollmentNote $note): bool
    {
        return $auth->role === UserRole::Admin
            || $auth->id === $note->author_id;
    }

    private function isAssignedCoach(User $coach, Enrollment $enrollment): bool
    {
        return $enrollment->certification->coaches()->where('users.id', $coach->id)->exists();
    }
}
