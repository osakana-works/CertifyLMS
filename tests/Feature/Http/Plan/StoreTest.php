<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Plan;

use App\Enums\PlanStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'テストプラン',
                'description' => 'テスト説明',
                'duration_days' => 90,
                'default_meeting_quota' => 5,
                'sort_order' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('plans', [
            'name' => 'テストプラン',
            'duration_days' => 90,
            'default_meeting_quota' => 5,
            'status' => PlanStatus::Draft->value,
            'created_by_user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_create_plan(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('admin.plans.store'), [
                'name' => 'テストプラン',
                'duration_days' => 90,
                'default_meeting_quota' => 5,
            ])
            ->assertForbidden();
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => '',
                'duration_days' => 90,
                'default_meeting_quota' => 5,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_duration_days_must_be_within_1_to_3650(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'テストプラン',
                'duration_days' => 3651,
                'default_meeting_quota' => 5,
            ])
            ->assertSessionHasErrors('duration_days');
    }

    public function test_default_meeting_quota_must_be_within_0_to_1000(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'テストプラン',
                'duration_days' => 90,
                'default_meeting_quota' => 1001,
            ])
            ->assertSessionHasErrors('default_meeting_quota');
    }
}
