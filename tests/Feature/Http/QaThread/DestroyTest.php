<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_delete_thread_without_replies(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();

        $this->actingAs($student)
            ->delete(route('qa-board.destroy', $thread))
            ->assertRedirect();

        $this->assertDatabaseMissing('qa_threads', ['id' => $thread->id]);
    }

    public function test_author_cannot_delete_thread_with_replies(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();
        $thread->replies()->create([
            'user_id' => User::factory()->coach()->create()->id,
            'body' => '回答です。',
        ]);

        $this->actingAs($student)
            ->delete(route('qa-board.destroy', $thread))
            ->assertForbidden();

        $this->assertDatabaseHas('qa_threads', ['id' => $thread->id]);
    }

    public function test_admin_can_delete_thread_with_replies_and_replies_are_cascaded(): void
    {
        $admin = User::factory()->admin()->create();
        $author = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($author)->create();
        $reply = $thread->replies()->create([
            'user_id' => User::factory()->coach()->create()->id,
            'body' => '回答です。',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.qa-board.destroy', $thread))
            ->assertRedirect();

        $this->assertDatabaseMissing('qa_threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('qa_replies', ['id' => $reply->id]);
    }
}
