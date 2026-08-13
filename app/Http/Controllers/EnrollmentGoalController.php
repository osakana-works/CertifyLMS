<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 受講登録(Enrollment)配下の個人目標。まずは画面確認用の最小構成。
 *
 * ヒアリング回答後、StoreRequest / StoreAction 等を追加して本実装する。
 */
final class EnrollmentGoalController extends Controller
{
    public function edit(EnrollmentGoal $goal): View
    {
        return view('enrollment-goal.edit', compact('goal'));
    }
}