<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * meetings テーブルに (coach_id, scheduled_at) UNIQUE 制約を追加する。
 *
 * 作成時マイグレーション(2026_05_25_000000_create_meetings_table)の doc-comment には
 * 「(coach_id, scheduled_at) UNIQUE で同コーチ×同時刻の二重予約を DB レベルで禁止」と
 * 記載されていたが、実際の制約定義が漏れていた。
 *
 * status を問わず coach_id + scheduled_at の組み合わせ全体に効く制約とすることで、
 * MeetingController::store() 側の UniqueConstraintViolationException catch が
 * 意図通りに機能するようにする。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->unique(['coach_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropUnique(['coach_id', 'scheduled_at']);
        });
    }
};
