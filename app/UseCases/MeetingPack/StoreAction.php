<?php

declare(strict_types=1);

namespace App\UseCases\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreAction
{
    /**
     * @param array{name: string, description?: ?string, meeting_count: int, price: int, stripe_price_id?: ?string, sort_order?: ?int} $validated
     */
    public function __invoke(User $createdBy, array $validated): MeetingPack
    {
        return DB::transaction(fn () => MeetingPack::create([
            ...$validated,
            'status' => MeetingPackStatus::Draft->value,
            'created_by_user_id' => $createdBy->id,
            'updated_by_user_id' => $createdBy->id,
        ]));
    }
}
