<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Section;
use App\Models\User;

final class LearningSectionPolicy
{
    public function view(User $user, Section $section): bool
    {
        return $user->enrollments()
            ->where('certification_id', $section->chapter->part->certification_id)
            ->where('status', '!=', EnrollmentStatus::Failed->value)
            ->exists();
    }
}
