<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Meeting;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\Meeting\MeetingCanceledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class MeetingCanceledNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_uses_database_and_mail(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $meeting = Meeting::factory()->canceled()->forStudent($student)->create();

        $channels = (new MeetingCanceledNotification($meeting, 'コーチ太郎'))->via($student);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_to_mail_contains_canceler_name_and_scheduled_at(): void
    {
        $student = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $meeting = Meeting::factory()->canceled()->forStudent($student)->create();

        $mail = (new MeetingCanceledNotification($meeting, 'コーチ太郎'))->toMail($student);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('【Certify LMS】面談がキャンセルされました', $mail->subject);
        $this->assertStringContainsString('受講生花子', $mail->greeting);
        $lines = implode(' ', $mail->introLines);
        $this->assertStringContainsString('コーチ太郎', $lines);
        $this->assertStringContainsString($meeting->scheduled_at->format('Y年n月j日'), $lines);
        $this->assertSame(route('meetings.show', $meeting->id), $mail->actionUrl);
    }

    public function test_to_database_has_expected_shape(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $meeting = Meeting::factory()->canceled()->forStudent($student)->create();

        $data = (new MeetingCanceledNotification($meeting, 'コーチ太郎'))->toDatabase($student);

        $this->assertSame('meeting_canceled', $data['notification_type']);
        $this->assertStringContainsString('コーチ太郎', $data['title']);
        $this->assertSame(route('meetings.show', $meeting->id), $data['action_url']);
    }
}
