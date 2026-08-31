<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_meeting_pack(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.store'), [
                'name' => 'テストパック',
                'description' => 'テスト説明',
                'meeting_count' => 3,
                'price' => 9000,
                'sort_order' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('meeting_packs', [
            'name' => 'テストパック',
            'meeting_count' => 3,
            'price' => 9000,
            'status' => MeetingPackStatus::Draft->value,
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_create_meeting_pack(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('admin.meeting-packs.store'), [
                'name' => 'テストパック',
                'meeting_count' => 3,
                'price' => 9000,
            ])
            ->assertForbidden();
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.store'), [
                'name' => '',
                'meeting_count' => 3,
                'price' => 9000,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_meeting_count_must_be_within_1_to_100(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.store'), [
                'name' => 'テストパック',
                'meeting_count' => 101,
                'price' => 9000,
            ])
            ->assertSessionHasErrors('meeting_count');
    }

    public function test_price_must_be_within_0_to_1000000(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.store'), [
                'name' => 'テストパック',
                'meeting_count' => 3,
                'price' => 1000001,
            ])
            ->assertSessionHasErrors('price');
    }
}
