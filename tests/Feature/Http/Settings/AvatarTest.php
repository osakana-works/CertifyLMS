<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar(): void
    {
        $user = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('avatar.png');

        $this->actingAs($user)
            ->post(route('settings.avatar.store'), [
                'avatar' => $file,
            ])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->avatar_url);
    }

    public function test_user_can_delete_avatar(): void
    {
        $user = User::factory()->admin()->create(['avatar_url' => 'http://localhost/storage/avatars/old.png']);

        $this->actingAs($user)
            ->delete(route('settings.avatar.destroy'))
            ->assertRedirect();

        $this->assertNull($user->fresh()->avatar_url);
    }

    public function test_non_image_file_is_rejected(): void
    {
        $user = User::factory()->admin()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $this->actingAs($user)
            ->post(route('settings.avatar.store'), [
                'avatar' => $file,
            ])
            ->assertSessionHasErrors('avatar');
    }

    public function test_file_over_2mb_is_rejected(): void
    {
        $user = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('avatar.png')->size(2049);

        $this->actingAs($user)
            ->post(route('settings.avatar.store'), [
                'avatar' => $file,
            ])
            ->assertSessionHasErrors('avatar');
    }
}
