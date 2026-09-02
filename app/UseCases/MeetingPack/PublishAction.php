<?php

declare(strict_types=1);

namespace App\UseCases\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Exceptions\MeetingPack\MeetingPackInvalidTransitionException;
use App\Models\MeetingPack;
use Illuminate\Support\Facades\DB;

final class PublishAction
{
    public function __invoke(MeetingPack $plan): MeetingPack
    {
        if ($plan->status !== MeetingPackStatus::Draft) {
            throw MeetingPackInvalidTransitionException::forPublish($plan->status);
        }

        return DB::transaction(function () use ($plan) {
            $plan->update([
                'status' => MeetingPackStatus::Published->value,
            ]);

            return $plan->fresh();
        });
    }
}
