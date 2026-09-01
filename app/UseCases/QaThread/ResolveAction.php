<?php

declare(strict_types=1);

namespace App\UseCases\QaThread;

use App\Enums\QaThreadStatus;
use App\Exceptions\QaThread\QaThreadInvalidTransitionException;
use App\Models\QaThread;
use Illuminate\Support\Facades\DB;

final class ResolveAction
{
    public function __invoke(QaThread $thread): QaThread
    {
        if ($thread->status !== QaThreadStatus::Unresolved) {
            throw QaThreadInvalidTransitionException::forResolve($thread->status);
        }

        return DB::transaction(function () use ($thread) {
            $thread->update([
                'status' => QaThreadStatus::Resolved->value,
                'resolved_at' => now(),
            ]);

            return $thread->fresh();
        });
    }
}