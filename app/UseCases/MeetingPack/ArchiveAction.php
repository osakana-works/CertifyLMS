<?php

declare(strict_types=1);

namespace App\UseCases\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Exceptions\MeetingPack\MeetingPackInvalidTransitionException;
use App\Models\MeetingPack;
use Illuminate\Support\Facades\DB;

final class ArchiveAction
{
    public function __invoke(MeetingPack $plan): MeetingPack
    {
        if ($plan->status !== MeetingPackStatus::Published) {
            throw MeetingPackInvalidTransitionException::forArchive($plan->status);
        }

        return DB::transaction(function () use ($plan) {
            $plan->update([
                'status' => MeetingPackStatus::Archived->value,
            ]);

            return $plan->fresh();
        });
    }
}
