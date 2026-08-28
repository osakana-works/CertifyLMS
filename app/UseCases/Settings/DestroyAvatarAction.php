<?php

declare(strict_types=1);

namespace App\UseCases\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DestroyAvatarAction
{
    public function __invoke(User $user): User
    {
        return DB::transaction(function () use ($user) {
            if ($user->avatar_url !== null) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            $user->update(['avatar_url' => null]);

            return $user->fresh();
        });
    }
}
