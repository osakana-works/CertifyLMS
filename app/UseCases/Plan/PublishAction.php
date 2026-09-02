<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Enums\PlanStatus;
use App\Exceptions\Plan\PlanInvalidTransitionException;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

final class PublishAction
{
    public function __invoke(Plan $plan): Plan
    {
        if ($plan->status !== PlanStatus::Draft) {
            throw PlanInvalidTransitionException::forPublish($plan->status);
        }

        return DB::transaction(function () use ($plan) {
            $plan->update([
                'status' => PlanStatus::Published->value,
            ]);

            return $plan->fresh();
        });
    }
}
