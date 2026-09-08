<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkAllAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_all_unread_as_read(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $first = $this->makeNotificationFor($student);
        $second = $this->makeNotificationFor($student);

        $this->actingAs($student)
            ->post(route('notifications.markAllAsRead'))
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNotNull($second->fresh()->read_at);
    }

    public function test_does_not_affect_other_users_notifications(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $other = User::factory()->student()->inProgress()->create();
        $othersNotification = $this->makeNotificationFor($other);

        $this->actingAs($student)->post(route('notifications.markAllAsRead'));

        $this->assertNull($othersNotification->fresh()->read_at);
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
