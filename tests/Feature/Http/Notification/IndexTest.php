<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_own_notifications_only(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $other = User::factory()->student()->inProgress()->create();

        $mine = $this->makeNotificationFor($student);
        $this->makeNotificationFor($other);

        $response = $this->actingAs($student)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('notifications', function ($notifications) use ($mine) {
            return $notifications->total() === 1 && $notifications->first()->id === $mine->id;
        });
    }

    public function test_unread_tab_filters_to_unread_only(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        $this->makeNotificationFor($student, read: true);
        $unread = $this->makeNotificationFor($student, read: false);

        $response = $this->actingAs($student)->get(route('notifications.index', ['tab' => 'unread']));

        $response->assertOk();
        $response->assertViewHas('notifications', function ($notifications) use ($unread) {
            return $notifications->total() === 1 && $notifications->first()->id === $unread->id;
        });
    }

    public function test_paginates_at_20_per_page(): void
    {
        $student = User::factory()->student()->inProgress()->create();
        for ($i = 0; $i < 25; $i++) {
            $this->makeNotificationFor($student);
        }

        $response = $this->actingAs($student)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->perPage() === 20
            && $notifications->total() === 25
            && $notifications->lastPage() === 2);
    }

    private function makeNotificationFor(User $user, bool $read = false): DatabaseNotification
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
        $notification->read_at = $read ? now() : null;
        $notification->save();

        return $notification;
    }
}
