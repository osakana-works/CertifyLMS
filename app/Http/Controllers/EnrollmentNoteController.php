<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EnrollmentNote;
use Illuminate\Contracts\View\View;

/**
 * 受講登録(Enrollment)配下のコーチメモ。まずは画面確認用の最小構成。
 *
 * ヒアリング回答後、StoreRequest / StoreAction 等を追加して本実装する。
 */
final class EnrollmentNoteController extends Controller
{
    public function edit(EnrollmentNote $note): View
    {
        return view('enrollment-note.edit', compact('note'));
    }
}