<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\QaThread;

use App\Models\QaThread;
use App\Models\User;
use App\Notifications\QaThread\QaThreadCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class QaThreadCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_uses_database_and_mail(): void
    {
        $coach = User::factory()->coach()->inProgress()->create();
        $thread = QaThread::factory()->create();

        $channels = (new QaThreadCreatedNotification($thread))->via($coach);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_to_mail_contains_certification_and_poster_name(): void
    {
        $coach = User::factory()->coach()->inProgress()->create(['name' => 'コーチ太郎']);
        $poster = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $thread = QaThread::factory()->forUser($poster)->create(['title' => '過去問の解き方について']);
        $thread->loadMissing('certification');

        $mail = (new QaThreadCreatedNotification($thread))->toMail($coach);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('【Certify LMS】新しい質問が投稿されました', $mail->subject);
        $this->assertStringContainsString('コーチ太郎', $mail->greeting);
        $lines = implode(' ', $mail->introLines);
        $this->assertStringContainsString('受講生花子', $lines);
        $this->assertStringContainsString($thread->certification->name, $lines);
        $this->assertSame(route('qa-board.show', $thread->id), $mail->actionUrl);
    }

    public function test_to_database_has_expected_shape(): void
    {
        $coach = User::factory()->coach()->inProgress()->create();
        $poster = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $thread = QaThread::factory()->forUser($poster)->create();

        $data = (new QaThreadCreatedNotification($thread))->toDatabase($coach);

        $this->assertSame('qa_thread_created', $data['notification_type']);
        $this->assertStringContainsString('受講生花子', $data['title']);
        $this->assertSame(route('qa-board.show', $thread->id), $data['action_url']);
    }
}
