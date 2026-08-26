<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\UserStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_draft_plan_without_active_users(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertRedirect();

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_published_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->published()->create();

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertForbidden();

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_draft_plan_with_active_user(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();
        User::factory()->student()->create([
            'plan_id' => $plan->id,
            'status' => UserStatus::InProgress->value,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertForbidden();

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_draft_plan_with_withdrawn_user(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();
        User::factory()->student()->create([
            'plan_id' => $plan->id,
            'status' => UserStatus::Withdrawn->value,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertForbidden();

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }
}
