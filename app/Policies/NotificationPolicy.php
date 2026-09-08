<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/**
 * 通知(Illuminate 標準の DatabaseNotification)の認可 Policy。
 *
 * 「本人宛ての通知か」のみを判定する。role や status による分岐は無い
 * (全ロールが自分宛ての通知を閲覧・既読化できる)。
 */
class NotificationPolicy
{
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_type === $user::class
            && $notification->notifiable_id === $user->id;
    }
}
