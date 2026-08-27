<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * EnrollmentGoal モデルのリレーション・Castを検証するUnitテスト。
 */
class EnrollmentGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_relation_returns_parent_enrollment(): void
    {
        $enrollment = Enrollment::factory()->create();
        $goal = EnrollmentGoal::factory()->forEnrollment($enrollment)->create();

        $this->assertTrue($goal->enrollment->is($enrollment));
    }

    public function test_achieved_at_cast_returns_carbon_instance(): void
    {
        $goal = EnrollmentGoal::factory()->achieved()->create();

        $this->assertInstanceOf(Carbon::class, $goal->achieved_at);
    }
}
