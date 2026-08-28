<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password'])->fresh();

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_update_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['current_password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['password']);
    }

    public function test_new_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'old-password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['password']);
    }
}
