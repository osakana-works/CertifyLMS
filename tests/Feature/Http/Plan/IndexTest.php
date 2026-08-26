<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Enums\UserStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_filters_by_name(): void
    {
        $admin = User::factory()->admin()->create();
        $matched = Plan::factory()->create(['name' => '特別プラン']);
        $unmatched = Plan::factory()->create(['name' => '通常プラン']);

        $response = $this->actingAs($admin)
            ->get(route('admin.plans.index', ['keyword' => '特別']));

        $response->assertSee($matched->name);
        $response->assertDontSee($unmatched->name);
    }

    public function test_status_filter_narrows_results(): void
    {
        $admin = User::factory()->admin()->create();
        $published = Plan::factory()->published()->create();
        $draft = Plan::factory()->draft()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.plans.index', ['status' => PlanStatus::Published->value]));

        $response->assertSee($published->name);
        $response->assertDontSee($draft->name);
    }

    public function test_non_admin_cannot_access_index(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('admin.plans.index'))
            ->assertForbidden();
    }

    public function test_users_count_only_counts_in_progress_users(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();

        User::factory()->student()->create([
            'plan_id' => $plan->id,
            'status' => UserStatus::InProgress->value,
        ]);
        User::factory()->student()->create([
            'plan_id' => $plan->id,
            'status' => UserStatus::Withdrawn->value,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.plans.index'));

        $response->assertSee('1 名');
    }
}
