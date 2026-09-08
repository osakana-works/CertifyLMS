<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\NotificationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_own_notification(): void
    {
        $user = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($user);

        $this->assertTrue((new NotificationPolicy)->view($user, $notification));
    }

    public function test_other_user_cannot_view_notification(): void
    {
        $owner = User::factory()->student()->inProgress()->create();
        $other = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($owner);

        $this->assertFalse((new NotificationPolicy)->view($other, $notification));
    }

    public function test_coach_cannot_view_students_notification(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $coach = User::factory()->coach()->inProgress()->create();
        $notification = $this->makeNotificationFor($student);

        $this->assertFalse((new NotificationPolicy)->view($coach, $notification));
    }

    private function makeNotificationFor(User $user): DatabaseNotification
    {
        $notification = new DatabaseNotification;
        $notification->id = (string) Str::uuid();
        $notification->type = 'App\\Notifications\\Chat\\ChatMessageReceivedNotification';
        $notification->notifiable_type = User::class;
        $notification->notifiable_id = $user->id;
        $notification->data = [
            'notification_type' => 'chat_message_received',
            'title' => 'テスト通知',
            'message' => 'テスト本文',
            'action_url' => 'https://example.test/',
        ];
        $notification->save();

        return $notification;
    }
}
