<?php

declare(strict_types=1);

namespace App\Notifications\Meeting;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 受講生が面談を予約した際に、割り当てられた担当コーチへ送る通知。
 *
 * database チャネルで通知一覧・ベル未読数に反映し、mail チャネルで即時にメール通知する。
 * 予約は受講生のみが行う操作のため、宛先はコーチ固定(受講生自身には送らない)。
 *
 * @see \App\Http\Controllers\MeetingController::store()
 */
class MeetingReservedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Meeting $meeting) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->meeting->loadMissing('student');

        return (new MailMessage)
            ->subject('【Certify LMS】面談の予約が入りました')
            ->greeting("{$notifiable->name} 様")
            ->line("{$this->meeting->student->name} さんから面談予約が入りました。")
            ->line("予約日時: {$this->meeting->scheduled_at->format('Y年n月j日 H:i')}")
            ->action('面談詳細を確認する', $this->actionUrl())
            ->salutation('Certify LMS 運営チーム');
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->meeting->loadMissing('student');

        return [
            'notification_type' => 'meeting_reserved',
            'title' => "{$this->meeting->student->name} さんから面談予約",
            'message' => "予約日時: {$this->meeting->scheduled_at->format('Y年n月j日 H:i')}",
            'action_url' => $this->actionUrl(),
        ];
    }

    private function actionUrl(): string
    {
        return route('meetings.show', $this->meeting->id);
    }
}