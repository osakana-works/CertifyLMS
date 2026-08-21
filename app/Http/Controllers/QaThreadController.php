<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CertificationStatus;
use App\Enums\QaThreadStatus;
use App\Enums\UserRole;
use App\Http\Requests\QaReply\StoreRequest as StoreReplyRequest;
use App\Http\Requests\QaReply\UpdateRequest as UpdateReplyRequest;
use App\Http\Requests\QaThread\StoreRequest;
use App\Http\Requests\QaThread\UpdateRequest;
use App\Models\Certification;
use App\Models\QaReply;
use App\Models\QaThread;
use App\UseCases\QaReply\DestroyAction as DestroyReplyAction;
use App\UseCases\QaReply\StoreAction as StoreReplyAction;
use App\UseCases\QaReply\UpdateAction as UpdateReplyAction;
use App\UseCases\QaThread\DestroyAction;
use App\UseCases\QaThread\IndexAction;
use App\UseCases\QaThread\ResolveAction;
use App\UseCases\QaThread\StoreAction;
use App\UseCases\QaThread\UnresolveAction;
use App\UseCases\QaThread\UpdateAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 質問掲示板(qa-board)。スレッドの CRUD・解決状態の切り替え・回答の CRUD を扱う。
 * 管理者向けモデレーション(admin.qa-board.*)も、同じメソッドを共有する形で対応する。
 */
final class QaThreadController extends Controller
{
    public function index(Request $request, IndexAction $action): View
    {
        $keyword = (string) $request->query('keyword', '');
        $certificationId = (string) $request->query('certification_id', '');
        $statusValue = (string) $request->query('status', '');
        $status = $statusValue !== '' ? QaThreadStatus::from($statusValue) : null;

        $viewer = $request->user();

        $threads = $action(
            viewer: $viewer,
            keyword: $keyword !== '' ? $keyword : null,
            certificationId: $certificationId !== '' ? $certificationId : null,
            status: $status,
        );

        $filters = $request->only(['status', 'certification_id', 'keyword']);

        $certifications = $viewer->role === UserRole::Admin
            ? Certification::orderBy('name')->get()
            : Certification::published()->orderBy('name')->get();

        $publishedStatus = CertificationStatus::Published;

        return view('qa-thread.index', compact('threads', 'filters', 'certifications', 'publishedStatus'));
    }

    public function create(): View
    {
        $this->authorize('create', QaThread::class);

        $certifications = Certification::published()->orderBy('name')->get();

        return view('qa-thread.create', compact('certifications'));
    }

    public function store(StoreRequest $request, StoreAction $action): RedirectResponse
    {
        $thread = $action($request->user(), $request->validated());

        return redirect()
            ->route('qa-board.show', $thread)
            ->with('success', 'スレッドを投稿しました。');
    }

    public function show(QaThread $thread): View
    {
        $thread->load(['certification', 'user', 'replies.user']);

        return view('qa-thread.show', compact('thread'));
    }

    public function edit(QaThread $thread): View
    {
        $this->authorize('update', $thread);

        return view('qa-thread.edit', compact('thread'));
    }

    public function update(QaThread $thread, UpdateRequest $request, UpdateAction $action): RedirectResponse
    {
        $action($thread, $request->validated());

        return redirect()
            ->route('qa-board.show', $thread)
            ->with('success', 'スレッドを更新しました。');
    }

    public function destroy(QaThread $thread, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $thread);

        $isAdminContext = request()->routeIs('admin.*');

        $action($thread);

        return redirect()
            ->route($isAdminContext ? 'admin.qa-board.index' : 'qa-board.index')
            ->with('success', 'スレッドを削除しました。');
    }

    public function resolve(QaThread $thread, ResolveAction $action): RedirectResponse
    {
        $this->authorize('resolve', $thread);

        $action($thread);

        return redirect()
            ->route('qa-board.show', $thread)
            ->with('success', 'スレッドを解決済みにしました。');
    }

    public function unresolve(QaThread $thread, UnresolveAction $action): RedirectResponse
    {
        $this->authorize('unresolve', $thread);

        $action($thread);

        return redirect()
            ->route('qa-board.show', $thread)
            ->with('success', 'スレッドを未解決に戻しました。');
    }

    public function storeReply(QaThread $thread, StoreReplyRequest $request, StoreReplyAction $action): RedirectResponse
    {
        $action($thread, $request->user(), $request->validated());

        return redirect()
            ->route('qa-board.show', $thread)
            ->with('success', '回答を投稿しました。');
    }

    public function editReply(QaThread $thread, QaReply $reply): View
    {
        $this->authorize('update', $reply);

        return view('qa-thread.reply-edit', compact('thread','reply'));
    }

    public function updateReply(QaThread $thread, QaReply $reply, UpdateReplyRequest $request, UpdateReplyAction $action): RedirectResponse
    {
        $action($reply, $request->validated());

        return redirect()
            ->route('qa-board.show', $reply->qa_thread_id)
            ->with('success', '回答を更新しました。');
    }

    public function destroyReply(QaThread $thread, QaReply $reply, DestroyReplyAction $action): RedirectResponse
    {
        $this->authorize('delete', $reply);

        $threadId = $reply->qa_thread_id;
        $isAdminContext = request()->routeIs('admin.*');

        $action($reply);

        return redirect()
            ->route($isAdminContext ? 'admin.qa-board.show' : 'qa-board.show', $threadId)
            ->with('success', '回答を削除しました。');
    }
}