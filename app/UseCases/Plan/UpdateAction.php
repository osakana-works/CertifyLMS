<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAction
{
    /**
     * @param array{name: string, description?: ?string, duration_days: int, default_meeting_quota: int, sort_order?: ?int} $validated
     */
    public function __invoke(Plan $plan, User $updatedBy, array $validated): Plan
    {
        return DB::transaction(function () use ($plan, $updatedBy, $validated) {
            $plan->update([
                ...$validated,
                'updated_by_user_id' => $updatedBy->id,
            ]);

            return $plan->fresh();
        });
    }
}
