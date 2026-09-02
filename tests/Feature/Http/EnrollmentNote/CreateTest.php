<?php

declare(strict_types=1);

namespace Tests\Feature\Http\EnrollmentNote;

use App\Models\Certification;
use App\Models\CertificationCoachAssignment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_coach_can_create_note(): void
    {
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->published()->create();
        $enrollment = Enrollment::factory()->create(['certification_id' => $certification->id]);

        CertificationCoachAssignment::create([
            'certification_id' => $certification->id,
            'user_id' => $coach->id,
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($coach)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => 'テストメモです。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_notes', [
            'enrollment_id' => $enrollment->id,
            'author_id' => $coach->id,
            'body' => 'テストメモです。',
        ]);
    }

    public function test_unassigned_coach_cannot_create_note(): void
    {
        $coach = User::factory()->coach()->create();
        $certification = Certification::factory()->published()->create();
        $enrollment = Enrollment::factory()->create(['certification_id' => $certification->id]);

        $this->actingAs($coach)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => 'テストメモです。',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_note_for_any_enrollment(): void
    {
        $admin = User::factory()->admin()->create();
        $enrollment = Enrollment::factory()->create();

        $this->actingAs($admin)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => '管理者メモです。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_notes', [
            'enrollment_id' => $enrollment->id,
            'author_id' => $admin->id,
        ]);
    }

    public function test_student_cannot_create_note(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => '不正なメモです。',
            ])
            ->assertForbidden();
    }

    public function test_body_is_required(): void
    {
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->published()->create();
        $enrollment = Enrollment::factory()->create(['certification_id' => $certification->id]);

        CertificationCoachAssignment::create([
            'certification_id' => $certification->id,
            'user_id' => $coach->id,
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($coach)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_body_cannot_exceed_2000_characters(): void
    {
        $coach = User::factory()->coach()->create();
        $admin = User::factory()->admin()->create();
        $certification = Certification::factory()->published()->create();
        $enrollment = Enrollment::factory()->create(['certification_id' => $certification->id]);

        CertificationCoachAssignment::create([
            'certification_id' => $certification->id,
            'user_id' => $coach->id,
            'assigned_by_user_id' => $admin->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($coach)
            ->post(route('enrollments.notes.store', $enrollment), [
                'body' => str_repeat('あ', 2001),
            ])
            ->assertSessionHasErrors('body');
    }
}
