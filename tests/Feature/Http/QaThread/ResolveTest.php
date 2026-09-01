<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Enums\QaThreadStatus;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_resolve_thread(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($student)->create();

        $this->actingAs($student)
            ->post(route('qa-board.resolve', $thread))
            ->assertRedirect();

        $this->assertSame(QaThreadStatus::Resolved, $thread->fresh()->status);
        $this->assertNotNull($thread->fresh()->resolved_at);
    }

    public function test_author_can_unresolve_thread(): void
    {
        $student = User::factory()->student()->create();
        $thread = QaThread::factory()->resolved()->forUser($student)->create();

        $this->actingAs($student)
            ->post(route('qa-board.unresolve', $thread))
            ->assertRedirect();

        $this->assertSame(QaThreadStatus::Unresolved, $thread->fresh()->status);
    }

    public function test_non_author_cannot_resolve_thread(): void
    {
        $author = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($author)->create();

        $this->actingAs($otherStudent)
            ->post(route('qa-board.resolve', $thread))
            ->assertForbidden();
    }
}
