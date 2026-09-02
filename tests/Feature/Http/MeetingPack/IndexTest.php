<?php

declare(strict_types=1);

namespace Tests\Feature\Http\MeetingPack;

use App\Enums\MeetingPackStatus;
use App\Models\MeetingPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_filters_by_name(): void
    {
        $admin = User::factory()->admin()->create();
        $matched = MeetingPack::factory()->create(['name' => '特別面談パック']);
        $unmatched = MeetingPack::factory()->create(['name' => '通常パック']);

        $response = $this->actingAs($admin)
            ->get(route('admin.meeting-packs.index', ['keyword' => '特別']));

        $response->assertSee($matched->name);
        $response->assertDontSee($unmatched->name);
    }

    public function test_status_filter_narrows_results(): void
    {
        $admin = User::factory()->admin()->create();
        $published = MeetingPack::factory()->published()->create();
        $draft = MeetingPack::factory()->draft()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.meeting-packs.index', ['status' => MeetingPackStatus::Published->value]));

        $response->assertSee($published->name);
        $response->assertDontSee($draft->name);
    }

    public function test_non_admin_cannot_access_index(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('admin.meeting-packs.index'))
            ->assertForbidden();
    }
}
