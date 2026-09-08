<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Chat;

use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\Chat\ChatMessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class ChatMessageReceivedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_uses_database_and_mail(): void
    {
        $recipient = User::factory()->coach()->inProgress()->create();
        $message = ChatMessage::factory()->create(['body' => 'テストメッセージ']);

        $channels = (new ChatMessageReceivedNotification($message))->via($recipient);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_to_mail_contains_sender_and_action_url(): void
    {
        $sender = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $recipient = User::factory()->coach()->inProgress()->create(['name' => 'コーチ太郎']);
        $message = ChatMessage::factory()->create([
            'sender_user_id' => $sender->id,
            'body' => 'こんにちは、質問があります。',
        ]);

        $mail = (new ChatMessageReceivedNotification($message))->toMail($recipient);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('【Certify LMS】新着メッセージが届いています', $mail->subject);
        $this->assertStringContainsString('コーチ太郎', $mail->greeting);
        $this->assertStringContainsString('受講生花子', implode(' ', $mail->introLines));
        $this->assertStringContainsString(
            route('chat.show', $message->chat_room_id),
            $mail->actionUrl,
        );
    }

    public function test_to_database_has_expected_shape(): void
    {
        $sender = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $recipient = User::factory()->coach()->inProgress()->create();
        $message = ChatMessage::factory()->create([
            'sender_user_id' => $sender->id,
            'body' => str_repeat('あ', 200),
        ]);

        $data = (new ChatMessageReceivedNotification($message))->toDatabase($recipient);

        $this->assertSame('chat_message_received', $data['notification_type']);
        $this->assertStringContainsString('受講生花子', $data['title']);
        $this->assertLessThanOrEqual(81, mb_strlen($data['message']));
        $this->assertSame(route('chat.show', $message->chat_room_id), $data['action_url']);
    }
}
