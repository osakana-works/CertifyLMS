<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 全ロール共通の設定・プロフィール画面。まずは画面確認用の最小構成(edit のみ)。
 *
 * ヒアリング回答後、UpdateRequest / UpdateAction 等を追加して store 以降を実装する。
 */
final class SettingsProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('settings.profile', compact('user'));
    }
}