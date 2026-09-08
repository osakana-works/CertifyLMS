<?php

declare(strict_types=1);

namespace App\UseCases\QaThread;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use App\Notifications\QaThread\QaThreadCreatedNotification;
use Illuminate\Support\Facades\DB;

/**
 * QaThread(質問)を新規投稿する Action。
 *
 * 投稿後、当該資格の担当コーチ全員へ通知(database + mail)を afterCommit で送る
 * (コーチのフォロー漏れ防止が目的のため、投稿者自身は当然対象外)。
 */
final class StoreAction
{
    /**
     * @param array{certification_id: string, title: string, body: string} $validated
     */
    public function __invoke(User $user, array $validated): QaThread
    {
        return DB::transaction(function () use ($user, $validated) {
            $thread = $user->qaThreads()->create([
                ...$validated,
                'status' => QaThreadStatus::Unresolved->value,
            ]);

            DB::afterCommit(function () use ($thread, $validated): void {
                $coaches = Certification::query()
                    ->whereKey($validated['certification_id'])
                    ->first()
                    ?->coaches
                    ?? collect();

                foreach ($coaches as $coach) {
                    $coach->notify(new QaThreadCreatedNotification($thread));
                }
            });

            return $thread;
        });
    }
}