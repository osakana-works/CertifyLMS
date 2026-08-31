<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MeetingPackStatus;
use App\Enums\UserRole;
use App\Models\MeetingPack;
use App\Models\User;

final class MeetingPackPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function create(User $auth): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function update(User $auth, MeetingPack $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function delete(User $auth, MeetingPack $plan): bool
    {
        if ($auth->role !== UserRole::Admin) {
            return false;
        }

        return $plan->status !== MeetingPackStatus::Published;
    }

    public function publish(User $auth, MeetingPack $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function archive(User $auth, MeetingPack $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function unarchive(User $auth, MeetingPack $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function view(User $auth, MeetingPack $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }
}
