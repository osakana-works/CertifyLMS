<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Meeting;

use App\Models\Meeting;
use App\Models\User;
use App\Notifications\Meeting\MeetingReservedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class MeetingReservedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_uses_database_and_mail(): void
    {
        $coach = User::factory()->coach()->inProgress()->create();
        $meeting = Meeting::factory()->reserved()->forCoach($coach)->create();

        $channels = (new MeetingReservedNotification($meeting))->via($coach);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_to_mail_contains_student_name_and_scheduled_at(): void
    {
        $coach = User::factory()->coach()->inProgress()->create(['name' => 'コーチ太郎']);
        $student = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $meeting = Meeting::factory()->reserved()->forCoach($coach)->forStudent($student)->create();

        $mail = (new MeetingReservedNotification($meeting))->toMail($coach);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('【Certify LMS】面談の予約が入りました', $mail->subject);
        $this->assertStringContainsString('コーチ太郎', $mail->greeting);
        $lines = implode(' ', $mail->introLines);
        $this->assertStringContainsString('受講生花子', $lines);
        $this->assertStringContainsString($meeting->scheduled_at->format('Y年n月j日'), $lines);
        $this->assertSame(route('meetings.show', $meeting->id), $mail->actionUrl);
    }

    public function test_to_database_has_expected_shape(): void
    {
        $coach = User::factory()->coach()->inProgress()->create();
        $student = User::factory()->student()->inProgress()->create(['name' => '受講生花子']);
        $meeting = Meeting::factory()->reserved()->forCoach($coach)->forStudent($student)->create();

        $data = (new MeetingReservedNotification($meeting))->toDatabase($coach);

        $this->assertSame('meeting_reserved', $data['notification_type']);
        $this->assertStringContainsString('受講生花子', $data['title']);
        $this->assertSame(route('meetings.show', $meeting->id), $data['action_url']);
    }
}
