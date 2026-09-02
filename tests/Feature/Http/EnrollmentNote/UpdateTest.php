<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentNote;

use App\Models\EnrollmentNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_update_own_note(): void
    {
        $coach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($coach)->create();

        $this->actingAs($coach)
            ->patch(route('enrollment-notes.update', $note), [
                'body' => '更新後のメモです。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_notes', [
            'id' => $note->id,
            'body' => '更新後のメモです。',
        ]);
    }

    public function test_other_coach_cannot_update_note(): void
    {
        $author = User::factory()->coach()->create();
        $otherCoach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($author)->create();

        $this->actingAs($otherCoach)
            ->patch(route('enrollment-notes.update', $note), [
                'body' => '不正な更新です。',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_any_note(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($coach)->create();

        $this->actingAs($admin)
            ->patch(route('enrollment-notes.update', $note), [
                'body' => '管理者による更新です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_notes', [
            'id' => $note->id,
            'body' => '管理者による更新です。',
        ]);
    }
}
