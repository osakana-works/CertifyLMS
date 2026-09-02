<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentGoal;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_goal(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => 'テスト目標',
                'target_date' => now()->addWeek()->format('Y-m-d'),
                'description' => 'テスト詳細',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_goals', [
            'enrollment_id' => $enrollment->id,
            'title' => 'テスト目標',
        ]);
    }

    public function test_non_owner_cannot_create_goal(): void
    {
        $owner = User::factory()->student()->create();
        $otherStudent = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherStudent)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => '不正な目標',
            ])
            ->assertForbidden();
    }

    public function test_title_is_required(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => '',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_title_cannot_exceed_100_characters(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => str_repeat('あ', 101),
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_description_cannot_exceed_1000_characters(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => 'テスト目標',
                'description' => str_repeat('あ', 1001),
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_target_date_must_be_valid_date(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => 'テスト目標',
                'target_date' => '不正な日付',
            ])
            ->assertSessionHasErrors('target_date');
    }

    public function test_target_date_can_be_in_the_past(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.goals.store', $enrollment), [
                'title' => 'テスト目標',
                'target_date' => now()->subMonth()->format('Y-m-d'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_goals', [
            'enrollment_id' => $enrollment->id,
            'title' => 'テスト目標',
        ]);
    }
}
