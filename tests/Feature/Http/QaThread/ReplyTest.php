<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\Certification;
use App\Models\CertificationCoachAssignment;
use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_post_reply(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();

        $this->actingAs($student)
            ->post(route('qa-board.replies.store', $thread), [
                'body' => 'テストの回答本文です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $thread->id,
            'user_id' => $student->id,
            'body' => 'テストの回答本文です。',
        ]);
    }

    public function test_assigned_coach_can_post_reply(): void
    {
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->published()->create();
        $thread = QaThread::factory()->forCertification($certification)->create();

        CertificationCoachAssignment::create([
            'certification_id' => $certification->id,
            'user_id' => $coach->id,
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($coach)
            ->post(route('qa-board.replies.store', $thread), [
                'body' => '担当資格への回答です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $thread->id,
            'user_id' => $coach->id,
        ]);
    }

    public function test_unassigned_coach_cannot_post_reply(): void
    {
        $coach = User::factory()->coach()->create();
        $certification = Certification::factory()->published()->create();
        $thread = QaThread::factory()->forCertification($certification)->create();

        $this->actingAs($coach)
            ->post(route('qa-board.replies.store', $thread), [
                'body' => '担当外資格への回答です。',
            ])
            ->assertForbidden();
    }

    public function test_author_can_update_own_reply(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->forUser($student)->create();

        $this->actingAs($student)
            ->patch(route('qa-board.replies.update', ['thread' => $thread, 'reply' => $reply]), [
                'body' => '更新後の回答本文です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('qa_replies', [
            'id' => $reply->id,
            'body' => '更新後の回答本文です。',
        ]);
    }

    public function test_non_author_cannot_update_reply(): void
    {
        $author = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->forUser($author)->create();

        $this->actingAs($otherStudent)
            ->patch(route('qa-board.replies.update', ['thread' => $thread, 'reply' => $reply]), [
                'body' => '不正な更新',
            ])
            ->assertForbidden();
    }

    public function test_author_can_delete_own_reply(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->forUser($student)->create();

        $this->actingAs($student)
            ->delete(route('qa-board.replies.destroy', ['thread' => $thread, 'reply' => $reply]))
            ->assertRedirect();

        $this->assertDatabaseMissing('qa_replies', ['id' => $reply->id]);
    }

    public function test_admin_can_delete_any_reply(): void
    {
        $admin = User::factory()->admin()->create();
        $author = User::factory()->student()->create();
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->forUser($author)->create();

        $this->actingAs($admin)
            ->delete(route('admin.qa-board.replies.destroy', ['thread' => $thread, 'reply' => $reply]))
            ->assertRedirect();

        $this->assertDatabaseMissing('qa_replies', ['id' => $reply->id]);
    }

    public function test_reply_body_is_required(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();

        $this->actingAs($student)
            ->post(route('qa-board.replies.store', $thread), [
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_reply_body_cannot_exceed_5000_characters(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->create();

        $this->actingAs($student)
            ->post(route('qa-board.replies.store', $thread), [
                'body' => str_repeat('あ', 5001),
            ])
            ->assertSessionHasErrors('body');
    }
}
