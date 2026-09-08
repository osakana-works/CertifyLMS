<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_own_notification(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($student);

        $this->actingAs($student)
            ->get(route('notifications.show', $notification))
            ->assertOk();
    }

    public function test_viewing_marks_as_read(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($student);

        $this->actingAs($student)->get(route('notifications.show', $notification));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_other_user_gets_403(): void
    {
        $owner = User::factory()->student()->inProgress()->create();
        $other = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($owner);

        $this->actingAs($other)
            ->get(route('notifications.show', $notification))
            ->assertForbidden();
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
            'action_url' => 'https://example.test/target',
        ];
        $notification->save();

        return $notification;
    }
}
