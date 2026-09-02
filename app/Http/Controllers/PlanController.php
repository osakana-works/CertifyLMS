<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PlanStatus;
use App\Http\Requests\Plan\StoreRequest;
use App\Http\Requests\Plan\UpdateRequest;
use App\Models\Plan;
use App\UseCases\Plan\ArchiveAction;
use App\UseCases\Plan\DestroyAction;
use App\UseCases\Plan\IndexAction;
use App\UseCases\Plan\PublishAction;
use App\UseCases\Plan\StoreAction;
use App\UseCases\Plan\UnarchiveAction;
use App\UseCases\Plan\UpdateAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * プランのマスタ管理(管理者専用)。CRUD・状態遷移(下書き→公開中→アーカイブ→下書き)を扱う。
 */
final class PlanController extends Controller
{
    public function index(Request $request, IndexAction $action): View
    {
        $this->authorize('viewAny', Plan::class);

        $keyword = (string) $request->query('keyword', '');
        $statusValue = (string) $request->query('status', '');
        $status = $statusValue !== '' ? PlanStatus::from($statusValue) : null;

        $plans = $action(
            keyword: $keyword !== '' ? $keyword : null,
            status: $status,
        );

        return view('plan.management.index', [
            'plans' => $plans,
            'keyword' => $keyword,
            'status' => $statusValue,
        ]);
    }

    public function show(Plan $plan): View
    {
        $this->authorize('view', $plan);

        return view('plan.management.show', compact('plan'));
    }

    public function create(): View
    {
        $this->authorize('create', Plan::class);

        return view('plan.management.create');
    }

    public function store(StoreRequest $request, StoreAction $action): RedirectResponse
    {
        $plan = $action($request->user(), $request->validated());

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'プランを作成しました。');
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('update', $plan);

        return view('plan.management.edit', compact('plan'));
    }

    public function update(Plan $plan, UpdateRequest $request, UpdateAction $action): RedirectResponse
    {
        $action($plan, $request->user(), $request->validated());

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'プランを更新しました。');
    }

    public function destroy(Plan $plan, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $action($plan);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'プランを削除しました。');
    }

    public function publish(Plan $plan, PublishAction $action): RedirectResponse
    {
        $this->authorize('publish', $plan);

        $action($plan);

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'プランを公開しました。');
    }

    public function archive(Plan $plan, ArchiveAction $action): RedirectResponse
    {
        $this->authorize('archive', $plan);

        $action($plan);

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'プランをアーカイブしました。');
    }

    public function unarchive(Plan $plan, UnarchiveAction $action): RedirectResponse
    {
        $this->authorize('unarchive', $plan);

        $action($plan);

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'プランを下書きに戻しました。');
    }
}
