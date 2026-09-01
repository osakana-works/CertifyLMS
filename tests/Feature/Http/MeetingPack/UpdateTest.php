<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_meeting_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->draft()->create();

        $this->actingAs($admin)
            ->patch(route('admin.meeting-packs.update', $plan), [
                'name' => '更新後のパック名',
                'meeting_count' => 5,
                'price' => 15000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('meeting_packs', [
            'id' => $plan->id,
            'name' => '更新後のパック名',
            'meeting_count' => 5,
            'price' => 15000,
            'updated_by_user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_update_meeting_pack(): void
    {
        $student = User::factory()->student()->create();
        $plan = MeetingPack::factory()->draft()->create();

        $this->actingAs($student)
            ->patch(route('admin.meeting-packs.update', $plan), [
                'name' => '不正な更新',
                'meeting_count' => 5,
                'price' => 15000,
            ])
            ->assertForbidden();
    }
}
