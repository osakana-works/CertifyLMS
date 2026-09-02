<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreAction
{
    /**
     * @param array{name: string, description?: ?string, duration_days: int, default_meeting_quota: int, sort_order?: ?int} $validated
     */
    public function __invoke(User $createdBy, array $validated): Plan
    {
        return DB::transaction(fn () => Plan::create([
            ...$validated,
            'status' => PlanStatus::Draft->value,
            'created_by_user_id' => $createdBy->id,
            'updated_by_user_id' => $createdBy->id,
        ]));
    }
}
