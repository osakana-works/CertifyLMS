<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

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
        $this->authorizeOwnNotification($request, $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return view('notifications.show', ['notification' => $notification]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->authorizeOwnNotification($request, $notification);

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

    private function authorizeOwnNotification(Request $request, DatabaseNotification $notification): void
    {
        $user = $request->user();

        abort_unless(
            $notification->notifiable_type === $user::class && $notification->notifiable_id === $user->id,
            403,
        );
    }
}