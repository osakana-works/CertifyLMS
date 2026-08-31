<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\QaThread;
use App\Models\User;

final class QaThreadPolicy
{
    /**
     * 受講生のみ投稿可能。
     */
    public function create(User $auth): bool
    {
        return $auth->role === UserRole::Student;
    }

    /**
     * 投稿者本人のみ編集可能。
     */
    public function update(User $auth, QaThread $thread): bool
    {
        return $auth->id === $thread->user_id;
    }

    /**
     * 投稿者本人: 回答が1件もない場合のみ削除可能。
     * 管理者: 常に削除可能(回答も含めて削除)。
     */
    public function delete(User $auth, QaThread $thread): bool
    {
        if ($auth->role === UserRole::Admin) {
            return true;
        }

        return $auth->id === $thread->user_id && ! $thread->replies()->exists();
    }

    /**
     * 投稿者本人のみ、解決/未解決を切り替え可能。
     */
    public function resolve(User $auth, QaThread $thread): bool
    {
        return $auth->id === $thread->user_id;
    }

    public function unresolve(User $auth, QaThread $thread): bool
    {
        return $auth->id === $thread->user_id;
    }
}