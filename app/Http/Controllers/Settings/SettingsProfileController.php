<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreAvatarRequest;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\UseCases\Settings\DestroyAvatarAction;
use App\UseCases\Settings\StoreAvatarAction;
use App\UseCases\Settings\UpdatePasswordAction;
use App\UseCases\Settings\UpdateProfileAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 全ロール共通の設定・プロフィール画面。プロフィール更新・パスワード変更・
 * アバター画像の変更を扱う。
 */
final class SettingsProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('settings.profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $action($request->user(), $request->validated());

        return redirect()
            ->route('settings.profile.edit')
            ->with('success', 'プロフィールを更新しました。');
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): RedirectResponse
    {
        $action($request->user(), $request->validated('password'));

        return redirect()
            ->route('settings.profile.edit', ['tab' => 'password'])
            ->with('success', 'パスワードを変更しました。');
    }

    public function storeAvatar(StoreAvatarRequest $request, StoreAvatarAction $action): RedirectResponse
    {
        $action($request->user(), $request->file('avatar'));

        return redirect()
            ->route('settings.profile.edit')
            ->with('success', 'アバター画像を更新しました。');
    }

    public function destroyAvatar(DestroyAvatarAction $action): RedirectResponse
    {
        $action(auth()->user());

        return redirect()
            ->route('settings.profile.edit')
            ->with('success', 'アバター画像を削除しました。');
    }
}
