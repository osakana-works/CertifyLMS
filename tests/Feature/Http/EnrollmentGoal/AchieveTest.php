<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchieveTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_mark_goal_as_achieved(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->create();

        $this->actingAs($student)
            ->post(route('enrollment-goals.markAchieved', $goal))
            ->assertRedirect();

        $this->assertNotNull($goal->fresh()->achieved_at);
    }

    public function test_owner_can_unmark_achieved_goal(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->achieved()->create();

        $this->actingAs($student)
            ->delete(route('enrollment-goals.unmarkAchieved', $goal))
            ->assertRedirect();

        $this->assertNull($goal->fresh()->achieved_at);
    }

    public function test_non_owner_cannot_mark_goal_as_achieved(): void
    {
        $owner = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $owner->id]);
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->create();

        $this->actingAs($otherStudent)
            ->post(route('enrollment-goals.markAchieved', $goal))
            ->assertForbidden();
    }
}
