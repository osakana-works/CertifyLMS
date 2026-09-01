<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_draft_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->draft()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.publish', $plan))
            ->assertRedirect();

        $this->assertSame(MeetingPackStatus::Published, $plan->fresh()->status);
    }

    public function test_admin_can_archive_published_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->published()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.archive', $plan))
            ->assertRedirect();

        $this->assertSame(MeetingPackStatus::Archived, $plan->fresh()->status);
    }

    public function test_admin_can_unarchive_archived_pack(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->archived()->create();

        $this->actingAs($admin)
            ->post(route('admin.meeting-packs.unarchive', $plan))
            ->assertRedirect();

        $this->assertSame(MeetingPackStatus::Draft, $plan->fresh()->status);
    }

    public function test_cannot_archive_draft_pack_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->draft()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.meeting-packs.archive', $plan))
            ->assertStatus(409);

        $this->assertSame(MeetingPackStatus::Draft, $plan->fresh()->status);
    }

    public function test_cannot_publish_archived_pack_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = MeetingPack::factory()->archived()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.meeting-packs.publish', $plan))
            ->assertStatus(409);

        $this->assertSame(MeetingPackStatus::Archived, $plan->fresh()->status);
    }
}
