<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Enums\PlanStatus;
use App\Exceptions\Plan\PlanInvalidTransitionException;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

final class ArchiveAction
{
    public function __invoke(Plan $plan): Plan
    {
        if ($plan->status !== PlanStatus::Published) {
            throw PlanInvalidTransitionException::forArchive($plan->status);
        }

        return DB::transaction(function () use ($plan) {
            $plan->update([
                'status' => PlanStatus::Archived->value,
            ]);

            return $plan->fresh();
        });
    }
}
