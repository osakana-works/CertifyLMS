<?php

declare(strict_types=1);

namespace App\UseCases\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateAction
{
    /**
     * @param array{name: string, description?: ?string, meeting_count: int, price: int, stripe_price_id?: ?string, sort_order?: ?int} $validated
     */
    public function __invoke(MeetingPack $plan, User $updatedBy, array $validated): MeetingPack
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
