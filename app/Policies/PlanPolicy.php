<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlanStatus;
use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

final class PlanPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function view(User $auth, Plan $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function create(User $auth): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function update(User $auth, Plan $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function delete(User $auth, Plan $plan): bool
    {
        if ($auth->role !== UserRole::Admin) {
            return false;
        }

        if ($plan->status !== PlanStatus::Draft) {
            return false;
        }

        return ! $plan->users()->exists();
    }

    public function publish(User $auth, Plan $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function archive(User $auth, Plan $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }

    public function unarchive(User $auth, Plan $plan): bool
    {
        return $auth->role === UserRole::Admin;
    }
}
