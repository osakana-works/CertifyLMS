<?php

declare(strict_types=1);

namespace App\UseCases\Settings;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class StoreAvatarAction
{
    public function __invoke(User $user, UploadedFile $avatar): User
    {
        return DB::transaction(function () use ($user, $avatar) {
            if ($user->avatar_url !== null) {
                Storage::disk('public')->delete($this->extractPath($user->avatar_url));
            }

            $path = $avatar->store('avatars', 'public');

            $user->update(['avatar_url' => Storage::disk('public')->url($path)]);

            return $user->fresh();
        });
    }

    private function extractPath(string $url): string
    {
        return ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/storage/');
    }
}
