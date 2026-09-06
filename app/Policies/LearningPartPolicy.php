<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Part;
use App\Models\User;

/**
 * 受講生向け教材ブラウジング(BrowseController)における、Partの閲覧認可ポリシー。
 * 受講登録の有無・状態(B-B-09)を判定する。資格の公開状態(B-B-03)は、
 * ShowPartActionの中で、引き続き判定する。
 */
final class LearningPartPolicy
{
    public function view(User $user, Part $part): bool
    {
        return $user->enrollments()
            ->where('certification_id', $part->certification_id)
            ->where('status', '!=', EnrollmentStatus::Failed->value)
            ->exists();
    }
}
