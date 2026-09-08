<?php

declare(strict_types=1);

namespace App\Notifications\Chat;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChatMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ChatMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->message->loadMissing('sender');

        return (new MailMessage)
            ->subject('【Certify LMS】新着メッセージが届いています')
            ->greeting("{$notifiable->name} 様")
            ->line("{$this->message->sender->name} さんからメッセージが届きました。")
            ->line($this->previewBody())
            ->action('チャットを開く', $this->actionUrl())
            ->salutation('Certify LMS 運営チーム');
    }

    public function toDatabase(object $notifiable): array
    {
        $this->message->loadMissing('sender');

        return [
            'notification_type' => 'chat_message_received',
            'title' => "{$this->message->sender->name} さんからメッセージ",
            'message' => $this->previewBody(),
            'action_url' => $this->actionUrl(),
        ];
    }

    private function previewBody(): string
    {
        return mb_strimwidth($this->message->body, 0, 80, '…');
    }

    private function actionUrl(): string
    {
        return route('chat.show', $this->message->chat_room_id);
    }
}