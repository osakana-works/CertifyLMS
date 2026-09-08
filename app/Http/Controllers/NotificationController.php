<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

/**
 * 通知一覧・既読化の Controller。student / coach / admin 共通(全ロール利用可能)。
 *
 * 通知の実体は Laravel 標準の Notifiable 機構(`notifications` テーブル)。
 * 認可は「本人宛の通知か」を都度チェックし、他人の通知は 403 で弾く。
 *
 * - index: タブ(全件 / 未読のみ)+ ページネーション
 * - markAsRead: 既読化 + data.action_url があればそこへ、無ければ notifications.show へ redirect
 * - markAllAsRead: 自分宛の未読を一括既読化
 * - show: action_url を持たない自己完結型通知の全文表示(閲覧時に既読化)
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab', 'all') === 'unread' ? 'unread' : 'all';

        $notifications = ($tab === 'unread' ? $user->unreadNotifications() : $user->notifications())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'tab' => $tab,
        ]);
    }

    public function show(Request $request, DatabaseNotification $notification): View
    {
        $this->authorize('view', $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return view('notifications.show', ['notification' => $notification]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->authorize('view', $notification);

        $notification->markAsRead();

        $data = is_array($notification->data) ? $notification->data : [];
        $actionUrl = $data['action_url'] ?? null;

        return $actionUrl !== null
            ? redirect($actionUrl)
            : redirect()->route('notifications.show', $notification);
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('success', '全ての通知を既読にしました。');
    }
}
