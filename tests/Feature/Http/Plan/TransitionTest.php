<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_draft_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.publish', $plan))
            ->assertRedirect();

        $this->assertSame(PlanStatus::Published, $plan->fresh()->status);
    }

    public function test_admin_can_archive_published_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->published()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.archive', $plan))
            ->assertRedirect();

        $this->assertSame(PlanStatus::Archived, $plan->fresh()->status);
    }

    public function test_admin_can_unarchive_archived_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->archived()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.unarchive', $plan))
            ->assertRedirect();

        $this->assertSame(PlanStatus::Draft, $plan->fresh()->status);
    }

    public function test_cannot_archive_draft_plan_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->draft()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.plans.archive', $plan))
            ->assertStatus(409);

        $this->assertSame(PlanStatus::Draft, $plan->fresh()->status);
    }

    public function test_cannot_publish_archived_plan_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->archived()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.plans.publish', $plan))
            ->assertStatus(409);

        $this->assertSame(PlanStatus::Archived, $plan->fresh()->status);
    }
}
