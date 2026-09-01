<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_goal(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->create();

        $this->actingAs($student)
            ->delete(route('enrollment-goals.destroy', $goal))
            ->assertRedirect();

        $this->assertDatabaseMissing('enrollment_goals', ['id' => $goal->id]);
    }

    public function test_non_owner_cannot_delete_goal(): void
    {
        $owner = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $owner->id]);
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->create();

        $this->actingAs($otherStudent)
            ->delete(route('enrollment-goals.destroy', $goal))
            ->assertForbidden();

        $this->assertDatabaseHas('enrollment_goals', ['id' => $goal->id]);
    }
}
