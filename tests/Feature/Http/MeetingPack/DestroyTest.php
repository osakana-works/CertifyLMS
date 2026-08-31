<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_draft_meeting_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->draft()->create();

        $this->actingAs($admin)
            ->delete(route('admin.meeting-packs.destroy', $plan))
            ->assertRedirect();

        $this->assertDatabaseMissing('meeting_packs', ['id' => $plan->id]);
    }

    public function test_admin_can_delete_archived_meeting_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->archived()->create();

        $this->actingAs($admin)
            ->delete(route('admin.meeting-packs.destroy', $plan))
            ->assertRedirect();

        $this->assertDatabaseMissing('meeting_packs', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_published_meeting_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->published()->create();

        $this->actingAs($admin)
            ->delete(route('admin.meeting-packs.destroy', $plan))
            ->assertForbidden();

        $this->assertDatabaseHas('meeting_packs', ['id' => $plan->id]);
    }
}
