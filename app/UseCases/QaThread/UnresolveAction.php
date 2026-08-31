<?php

declare(strict_types=1);

namespace App\UseCases\QaThread;

use App\Enums\QaThreadStatus;
use App\Exceptions\QaThread\QaThreadInvalidTransitionException;
use App\Models\QaThread;
use Illuminate\Support\Facades\DB;

final class UnresolveAction
{
    public function __invoke(QaThread $thread): QaThread
    {
        if ($thread->status !== QaThreadStatus::Resolved) {
            throw QaThreadInvalidTransitionException::forUnresolve($thread->status);
        }

        return DB::transaction(function () use ($thread) {
            $thread->update([
                'status' => QaThreadStatus::Unresolved->value,
                'resolved_at' => null,
            ]);

            return $thread->fresh();
        });
    }
}