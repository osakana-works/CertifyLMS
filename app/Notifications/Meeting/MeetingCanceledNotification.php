<?php

declare(strict_types=1);

namespace App\Notifications\Meeting;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 面談がキャンセルされた際に、キャンセルした本人以外の相手方へ送る通知。
 *
 * database チャネルで通知一覧・ベル未読数に反映し、mail チャネルで即時にメール通知する。
 * 受講生 / コーチのどちらがキャンセルしたかは呼出側で判定し、相手方だけを notifiable として渡す。
 *
 * @see \App\Http\Controllers\MeetingController::cancel()
 */
class MeetingCanceledNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Meeting $meeting,
        private readonly string $canceledByName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【Certify LMS】面談がキャンセルされました')
            ->greeting("{$notifiable->name} 様")
            ->line("{$this->canceledByName} さんが面談をキャンセルしました。")
            ->line("予定日時: {$this->meeting->scheduled_at->format('Y年n月j日 H:i')}")
            ->action('面談詳細を確認する', $this->actionUrl())
            ->salutation('Certify LMS 運営チーム');
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => 'meeting_canceled',
            'title' => "{$this->canceledByName} さんが面談をキャンセル",
            'message' => "予定日時: {$this->meeting->scheduled_at->format('Y年n月j日 H:i')}",
            'action_url' => $this->actionUrl(),
        ];
    }

    private function actionUrl(): string
    {
        return route('meetings.show', $this->meeting->id);
    }
}