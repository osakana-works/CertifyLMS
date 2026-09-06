<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Chapter;
use App\Models\User;

final class LearningChapterPolicy
{
    public function view(User $user, Chapter $chapter): bool
    {
        return $user->enrollments()
            ->where('certification_id', $chapter->part->certification_id)
            ->where('status', '!=', EnrollmentStatus::Failed->value)
            ->exists();
    }
}
