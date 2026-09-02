<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentNote;

use App\Models\EnrollmentNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_delete_own_note(): void
    {
        $coach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($coach)->create();

        $this->actingAs($coach)
            ->delete(route('enrollment-notes.destroy', $note))
            ->assertRedirect();

        $this->assertDatabaseMissing('enrollment_notes', ['id' => $note->id]);
    }

    public function test_other_coach_cannot_delete_note(): void
    {
        $author = User::factory()->coach()->create();
        $otherCoach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($author)->create();

        $this->actingAs($otherCoach)
            ->delete(route('enrollment-notes.destroy', $note))
            ->assertForbidden();

        $this->assertDatabaseHas('enrollment_notes', ['id' => $note->id]);
    }

    public function test_admin_can_delete_any_note(): void
    {
        $admin = User::factory()->admin()->create();
        $coach = User::factory()->coach()->create();
        $note = EnrollmentNote::factory()->forAuthor($coach)->create();

        $this->actingAs($admin)
            ->delete(route('enrollment-notes.destroy', $note))
            ->assertRedirect();

        $this->assertDatabaseMissing('enrollment_notes', ['id' => $note->id]);
    }
}
