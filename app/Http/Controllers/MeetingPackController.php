<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MeetingPackStatus;
use App\Http\Requests\MeetingPack\StoreRequest;
use App\Http\Requests\MeetingPack\UpdateRequest;
use App\Models\MeetingPack;
use App\UseCases\MeetingPack\ArchiveAction;
use App\UseCases\MeetingPack\DestroyAction;
use App\UseCases\MeetingPack\IndexAction;
use App\UseCases\MeetingPack\PublishAction;
use App\UseCases\MeetingPack\StoreAction;
use App\UseCases\MeetingPack\UnarchiveAction;
use App\UseCases\MeetingPack\UpdateAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 面談パックのマスタ管理(管理者専用)。CRUD・状態遷移(下書き→公開中→アーカイブ→下書き)を扱う。
 */
class MeetingPackController extends Controller
{
    public function index(Request $request, IndexAction $action): View
    {
        $this->authorize('viewAny', MeetingPack::class);

        $keyword = (string) $request->query('keyword', '');
        $statusValue = (string) $request->query('status', '');
        $status = $statusValue !== '' ? MeetingPackStatus::from($statusValue) : null;

        $plans = $action(
            keyword: $keyword !== '' ? $keyword : null,
            status: $status,
        );

        return view('meeting-pack.management.index', [
            'plans' => $plans,
            'keyword' => $keyword,
            'status' => $statusValue,  // ← Viewには、文字列のまま渡す
        ]);
    }

    public function show(MeetingPack $plan): View
    {
        $this->authorize('view', $plan);

        return view('meeting-pack.management.show', compact('plan'));
    }

    public function create(): View
    {
        $this->authorize('create', MeetingPack::class);

        return view('meeting-pack.management.create');
    }

    public function store(StoreRequest $request, StoreAction $action): RedirectResponse
    {
        $plan = $action($request->user(), $request->validated());

        return redirect()
            ->route('admin.meeting-packs.show', $plan)
            ->with('success', '面談パックを作成しました。');
    }

    public function edit(MeetingPack $plan): View
    {
        $this->authorize('update', $plan);

        return view('meeting-pack.management.edit', compact('plan'));
    }

    public function update(MeetingPack $plan, UpdateRequest $request, UpdateAction $action): RedirectResponse
    {
        $action($plan, $request->user(), $request->validated());

        return redirect()
            ->route('admin.meeting-packs.show', $plan)
            ->with('success', '面談パックを更新しました。');
    }

    public function destroy(MeetingPack $plan, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $action($plan);

        return redirect()
            ->route('admin.meeting-packs.index')
            ->with('success', '面談パックを削除しました。');
    }

    public function publish(MeetingPack $plan, PublishAction $action): RedirectResponse
    {
        $this->authorize('publish', $plan);

        $action($plan);

        return redirect()
            ->route('admin.meeting-packs.show', $plan)
            ->with('success', '面談パックを公開しました。');
    }

    public function archive(MeetingPack $plan, ArchiveAction $action): RedirectResponse
    {
        $this->authorize('archive', $plan);

        $action($plan);

        return redirect()
            ->route('admin.meeting-packs.show', $plan)
            ->with('success', '面談パックをアーカイブしました。');
    }

    public function unarchive(MeetingPack $plan, UnarchiveAction $action): RedirectResponse
    {
        $this->authorize('unarchive', $plan);

        $action($plan);

        return redirect()
            ->route('admin.meeting-packs.show', $plan)
            ->with('success', '面談パックを下書きに戻しました。');
    }
}
