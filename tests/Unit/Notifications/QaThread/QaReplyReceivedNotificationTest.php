<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\QaThread;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use App\Notifications\QaThread\QaReplyReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class QaReplyReceivedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_uses_database_and_mail(): void
    {
        $recipient = User::factory()->student()->inProgress()->create();
        $reply = QaReply::factory()->create();

        $channels = (new QaReplyReceivedNotification($reply))->via($recipient);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_to_mail_contains_thread_title_and_replier_name(): void
    {
        $recipient = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $thread = QaThread::factory()->create(['title' => '過去問の解き方について']);
        $replier = User::factory()->coach()->inProgress()->create(['name' => 'コーチ太郎']);
        $reply = QaReply::factory()->forThread($thread)->forUser($replier)->create();

        $mail = (new QaReplyReceivedNotification($reply))->toMail($recipient);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('【Certify LMS】質問に回答が届いています', $mail->subject);
        $this->assertStringContainsString('受講生花子', $mail->greeting);
        $lines = implode(' ', $mail->introLines);
        $this->assertStringContainsString('過去問の解き方について', $lines);
        $this->assertStringContainsString('コーチ太郎', $lines);
        $this->assertSame(route('qa-board.show', $thread->id), $mail->actionUrl);
    }

    public function test_to_database_has_expected_shape(): void
    {
        $recipient = User::factory()->student()->inProgress()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->create(['body' => 'ご質問への回答です。']);

        $data = (new QaReplyReceivedNotification($reply))->toDatabase($recipient);

        $this->assertSame('qa_reply_received', $data['notification_type']);
        $this->assertSame(route('qa-board.show', $thread->id), $data['action_url']);
    }
}
