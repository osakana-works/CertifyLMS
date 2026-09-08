<?php

declare(strict_types=1);

namespace App\Notifications\QaThread;

use App\Models\QaThread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 担当資格に新しい QaThread(質問)が投稿された際に、担当コーチへ送る通知。
 *
 * database チャネルで通知一覧・ベル未読数に反映し、mail チャネルで即時にメール通知する。
 * 「コーチのフォロー漏れ防止」が目的のため、投稿直後(未回答の状態)に送る。
 *
 * @see \App\UseCases\QaThread\StoreAction
 */
class QaThreadCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly QaThread $thread) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->thread->loadMissing(['user', 'certification']);

        return (new MailMessage)
            ->subject('【Certify LMS】新しい質問が投稿されました')
            ->greeting("{$notifiable->name} 様")
            ->line("担当資格「{$this->thread->certification->name}」に、{$this->thread->user->name} さんから新しい質問が投稿されました。")
            ->line($this->previewBody())
            ->action('質問を確認する', $this->actionUrl())
            ->salutation('Certify LMS 運営チーム');
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->thread->loadMissing(['user', 'certification']);

        return [
            'notification_type' => 'qa_thread_created',
            'title' => "{$this->thread->user->name} さんから新しい質問",
            'message' => $this->previewBody(),
            'action_url' => $this->actionUrl(),
        ];
    }

    private function previewBody(): string
    {
        return mb_strimwidth($this->thread->title, 0, 80, '…');
    }

    private function actionUrl(): string
    {
        return route('qa-board.show', $this->thread->id);
    }
}