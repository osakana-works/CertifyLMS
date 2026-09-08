<?php

declare(strict_types=1);

namespace App\UseCases\QaReply;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use App\Notifications\QaThread\QaReplyReceivedNotification;
use Illuminate\Support\Facades\DB;

/**
 * QaThread に回答を新規投稿する Action。
 *
 * 投稿後、スレッド投稿者本人以外が回答した場合に限り、スレッド投稿者へ
 * 通知(database + mail)を afterCommit で送る(自分のスレッドへの自己返信は通知しない)。
 */
final class StoreAction
{
    /**
     * @param array{body: string} $validated
     */
    public function __invoke(QaThread $thread, User $user, array $validated): QaReply
    {
        return DB::transaction(function () use ($thread, $user, $validated) {
            $reply = $thread->replies()->create([
                ...$validated,
                'user_id' => $user->id,
            ]);

            DB::afterCommit(function () use ($reply, $thread, $user): void {
                if ($thread->user_id !== $user->id) {
                    $thread->user->notify(new QaReplyReceivedNotification($reply));
                }
            });

            return $reply;
        });
    }
}