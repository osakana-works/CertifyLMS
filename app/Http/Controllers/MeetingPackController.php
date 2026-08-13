<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\MeetingPack;

class MeetingPackController extends Controller
{
    /**
     * 面談パックのマスタ管理(管理者専用)。まずは画面確認用の最小構成(index のみ)。
     *
     * ヒアリング回答後、StoreRequest / StoreAction 等を追加して store 以降を実装する。
     */
    public function index(Request $request): View
    {   
        // TODO: ヒアリング回答後、app/UseCases/MeetingPack/IndexAction に差し替える
        $plans = MeetingPack::query()->ordered()->paginate(20);

        $keyword = (string) $request->query('keyword', '');
        $status = (string) $request->query('status', '');

        return view('meeting-pack.management.index', compact('plans', 'keyword', 'status'));
    }

    public function show(MeetingPack $plan): View
    {
        return view('meeting-pack.management.show', compact('plan'));
    }

    public function create  (): View
    {
        return view('meeting-pack.management.create');
    }
}
