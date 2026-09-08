<?php

declare(strict_types=1);

namespace App\Notifications\QaThread;

use App\Models\QaReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 自分の投稿した QaThread に新しい回答が付いた際に、スレッド投稿者へ送る通知。
 *
 * database チャネルで通知一覧・ベル未読数に反映し、mail チャネルで即時にメール通知する。
 * 回答者自身がスレッド投稿者本人の場合は呼出側で対象から除外する。
 *
 * @see \App\UseCases\QaReply\StoreAction
 */
class QaReplyReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly QaReply $reply) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->reply->loadMissing(['user', 'qaThread']);

        return (new MailMessage)
            ->subject('【Certify LMS】質問に回答が届いています')
            ->greeting("{$notifiable->name} 様")
            ->line("「{$this->reply->qaThread->title}」に {$this->reply->user->name} さんから回答が届きました。")
            ->line($this->previewBody())
            ->action('質問を確認する', $this->actionUrl())
            ->salutation('Certify LMS 運営チーム');
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->reply->loadMissing(['user', 'qaThread']);

        return [
            'notification_type' => 'qa_reply_received',
            'title' => "「{$this->reply->qaThread->title}」に回答が届きました",
            'message' => $this->previewBody(),
            'action_url' => $this->actionUrl(),
        ];
    }

    private function previewBody(): string
    {
        return mb_strimwidth($this->reply->body, 0, 80, '…');
    }

    private function actionUrl(): string
    {
        return route('qa-board.show', $this->reply->qa_thread_id);
    }
}