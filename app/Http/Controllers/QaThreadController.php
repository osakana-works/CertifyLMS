<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CertificationStatus;
use App\Models\Certification;
use App\Models\QaThread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * 質問掲示板。まずは画面確認用の最小構成(index のみ)。
 *
 * ヒアリング回答後、StoreRequest / StoreAction 等を追加して store 以降を実装する。
 */
final class QaThreadController extends Controller
{
    public function index(Request $request): View
    {
        // TODO: ヒアリング回答後、app/UseCases/QaThread/IndexAction に差し替える
        $threads = QaThread::query()->paginate(20);

        $filters = $request->only(['status', 'certification_id', 'keyword']);

        $certifications = Certification::published()->orderBy('name')->get();

        $publishedStatus = CertificationStatus::Published;

        return view('qa-thread.index', compact('threads', 'filters', 'certifications', 'publishedStatus'));
    }
}