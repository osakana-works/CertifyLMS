<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_access_profile_edit_page(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('settings.profile.edit'))
            ->assertOk()
            ->assertViewIs('settings.profile')
            ->assertViewHas('user', $admin);
    }

    public function test_logged_in_user_can_update_profile_name(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => 'New Name',
            ])
            ->assertRedirect(route('settings.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'New Name',
        ]);
    }

    public function test_logged_in_user_can_update_profile_bio(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => $admin->name,
                'bio' => 'New Bio',
            ])
            ->assertRedirect(route('settings.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'bio' => 'New Bio',
        ]);
    }

    public function test_coach_can_update_profile_fixed_meeting_url(): void
    {
        $coach = User::factory()->coach()->create(['meeting_url' => 'https://example.com']);
        $this->actingAs($coach)
            ->patch(route('settings.profile.update'), [
                'name' => $coach->name,
                'meeting_url' => 'https://example.com/meeting',
            ])
            ->assertRedirect(route('settings.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $coach->id,
            'meeting_url' => 'https://example.com/meeting',
        ]);
    }

    public function test_non_coach_cannot_update_profile_fixed_meeting_url(): void
    {
        $admin = User::factory()->admin()->create(['meeting_url' => 'https://example.com']);
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => $admin->name,
                'meeting_url' => 'https://example.com/meeting',
            ])
            ->assertRedirect(route('settings.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'meeting_url' => 'https://example.com',
        ]);
    }

    public function test_email_address_is_ignored_when_updating_profile(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => $admin->name,
                'email' => 'new-email@example.com',
            ])
            ->assertRedirect(route('settings.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'admin@example.com',
        ]);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => '',
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_name_cannot_exceed_50_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => str_repeat('a', 51),
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_bio_cannot_exceed_1000_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->patch(route('settings.profile.update'), [
                'name' => $admin->name,
                'bio' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors(['bio']);
    }
}
