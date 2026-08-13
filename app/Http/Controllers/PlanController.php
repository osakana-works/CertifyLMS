<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * プランのマスタ管理(管理者専用)。まずは画面確認用の最小構成(index のみ)。
 *
 * ヒアリング回答後、StoreRequest / StoreAction 等を追加して store 以降を実装する。
 */
final class PlanController extends Controller
{
    public function index(Request $request): View
    {
        // TODO: ヒアリング回答後、app/UseCases/Plan/IndexAction に差し替える
        $plans = Plan::query()->withCount('users')->ordered()->paginate(20);

        $keyword = (string) $request->query('keyword', '');
        $status = (string) $request->query('status', '');

        return view('plan.management.index', compact('plans', 'keyword', 'status'));
    }

    public function create(): View
    {
        return view('plan.management.create');
    }
}