<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_own_thread(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();

        $this->actingAs($student)
            ->patch(route('qa-board.update', $thread), [
                'title' => '更新後のタイトル',
                'body' => '更新後の本文です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('qa_threads', [
            'id' => $thread->id,
            'title' => '更新後のタイトル',
        ]);
    }

    public function test_non_author_cannot_update_thread(): void
    {
        $author = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($author)->create();

        $this->actingAs($otherStudent)
            ->patch(route('qa-board.update', $thread), [
                'title' => '不正な更新',
                'body' => '不正な本文',
            ])
            ->assertForbidden();
    }

    public function test_title_is_required_on_update(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();

        $this->actingAs($student)
            ->patch(route('qa-board.update', $thread), [
                'title' => '',
                'body' => '更新後の本文です。',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_body_is_required_on_update(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();

        $this->actingAs($student)
            ->patch(route('qa-board.update', $thread), [
                'title' => '更新後のタイトル',
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_certification_id_is_ignored_on_update(): void
    {
        $student = User::factory()->student()->create();
        $originalCertification = Certification::factory()->published()->create();
        $otherCertification = Certification::factory()->published()->create();
        $thread = QaThread::factory()->forUser($student)->forCertification($originalCertification)->create();

        $this->actingAs($student)
            ->patch(route('qa-board.update', $thread), [
                'certification_id' => $otherCertification->id,
                'title' => '更新後のタイトル',
                'body' => '更新後の本文です。',
            ])
            ->assertRedirect();

        $this->assertSame($originalCertification->id, $thread->fresh()->certification_id);
    }
}
