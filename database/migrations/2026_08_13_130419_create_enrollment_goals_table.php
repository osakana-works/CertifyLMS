<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 受講登録(Enrollment)配下の個人目標。受講生本人が資格ごとに自由入力で立てる。
 *
 * achieved_at が非null = 達成済み。物理削除のみ(履歴は残さない)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_goals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();

            $table->index('enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_goals');
    }
};
