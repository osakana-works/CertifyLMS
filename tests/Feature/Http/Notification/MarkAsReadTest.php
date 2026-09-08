<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_as_read_and_redirects_to_action_url(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($student, actionUrl: 'https://example.test/target');

        $response = $this->actingAs($student)->post(route('notifications.markAsRead', $notification));

        $response->assertRedirect('https://example.test/target');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_redirects_to_show_when_no_action_url(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($student, actionUrl: null);

        $response = $this->actingAs($student)->post(route('notifications.markAsRead', $notification));

        $response->assertRedirect(route('notifications.show', $notification));
    }

    public function test_other_user_gets_403_and_notification_remains_unread(): void
    {
        $owner = User::factory()->student()->inProgress()->create();
        $other = User::factory()->student()->inProgress()->create();
        $notification = $this->makeNotificationFor($owner, actionUrl: 'https://example.test/target');

        $this->actingAs($other)
            ->post(route('notifications.markAsRead', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    private function makeNotificationFor(User $user, ?string $actionUrl): DatabaseNotification
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
            'action_url' => $actionUrl,
        ];
        $notification->save();

        return $notification;
    }
}
