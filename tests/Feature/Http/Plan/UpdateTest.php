<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();

        $this->actingAs($admin)
            ->put(route('admin.plans.update', $plan), [
                'name' => '更新後のプラン名',
                'duration_days' => 180,
                'default_meeting_quota' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => '更新後のプラン名',
            'duration_days' => 180,
            'default_meeting_quota' => 10,
            'updated_by_user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_update_plan(): void
    {
        $student = User::factory()->student()->create();
        $plan = Plan::factory()->draft()->create();

        $this->actingAs($student)
            ->put(route('admin.plans.update', $plan), [
                'name' => '不正な更新',
                'duration_days' => 180,
                'default_meeting_quota' => 10,
            ])
            ->assertForbidden();
    }
}
