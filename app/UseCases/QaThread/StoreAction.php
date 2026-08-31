<?php

declare(strict_types=1);

namespace App\UseCases\QaThread;

use App\Enums\QaThreadStatus;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreAction
{
    /**
     * @param array{certification_id: string, title: string, body: string} $validated
     */
    public function __invoke(User $user, array $validated): QaThread
    {
        return DB::transaction(fn () => $user->qaThreads()->create([
            ...$validated,
            'status' => QaThreadStatus::Unresolved->value,
        ]));
    }
}