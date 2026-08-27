<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;

final class EnrollmentGoalPolicy
{
    public function create(User $auth, Enrollment $enrollment): bool
    {
        return $auth->id === $enrollment->user_id;
    }

    public function update(User $auth, EnrollmentGoal $goal): bool
    {
        return $auth->id === $goal->enrollment->user_id;
    }

    public function delete(User $auth, EnrollmentGoal $goal): bool
    {
        return $auth->id === $goal->enrollment->user_id;
    }

    public function markAchieved(User $auth, EnrollmentGoal $goal): bool
    {
        return $auth->id === $goal->enrollment->user_id;
    }

    public function unmarkAchieved(User $auth, EnrollmentGoal $goal): bool
    {
        return $auth->id === $goal->enrollment->user_id;
    }
}
