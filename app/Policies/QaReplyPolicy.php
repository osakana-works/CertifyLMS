<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certification;
use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;

final class QaReplyPolicy
{
    /**
     * 受講生: 未受講でも投稿可能。
     * コーチ: 担当資格のスレッドのみ投稿可能。
     */
    public function create(User $auth, QaThread $thread): bool
    {
        return match ($auth->role) {
            UserRole::Student => true,
            UserRole::Coach => $this->assignedCoach($auth, $thread->certification),
            default => false,
        };
    }

    /**
     * 投稿者本人のみ編集可能(役職は問わない)。
     */
    public function update(User $auth, QaReply $reply): bool
    {
        return $auth->id === $reply->user_id;
    }

    /**
     * 投稿者本人: 無条件で削除可能。
     * 管理者: 常に削除可能。
     */
    public function delete(User $auth, QaReply $reply): bool
    {
        if ($auth->role === UserRole::Admin) {
            return true;
        }

        return $auth->id === $reply->user_id;
    }

    private function assignedCoach(User $coach, Certification $certification): bool
    {
        return $certification->coaches()->where('users.id', $coach->id)->exists();
    }
}