<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * 開発用 qa-board シーダー。
 *
 * **設計思想**:
 *
 * 1. **公開済の資格ごとに、未回答・対応中・解決済のスレッドを確実に散布**: 一覧の状態バッジ
 *    (未回答 / 対応中 / 解決済)を、どの資格を見ても実機確認できるよう、各資格に3パターンを
 *    最低1件ずつ確実に投入したうえで、追加のバリエーションをランダムに散布する。
 * 2. **回答 0 件 / 数件のスレッドを混在**: 削除ボタンの表示条件(回答がある投稿者本人は削除不可)を
 *    実機確認できるよう、回答件数を状態に応じて調整する。
 * 3. **固定 student のスレッドを確実に用意**: student@certify-lms.test を投稿者にした、未解決・解決済み
 *    それぞれ 1 件以上のスレッドを、シナリオとして明示的に投入する(自分の質問一覧・解決マークの動線確認用)。
 *
 * 依存順序: `UserSeeder` → `CertificationSeeder` → `EnrollmentSeeder` → 本 Seeder。
 */
final class QaThreadSeeder extends Seeder
{
    public function run(): void
    {
        $certifications = Certification::published()->get();

        if ($certifications->isEmpty()) {
            $this->command?->warn('QaThreadSeeder: 公開済みの資格が存在しません。先にCertificationSeederを実行してください。');

            return;
        }

        foreach ($certifications as $certification) {
            $this->seedThreadsForCertification($certification);
        }

        $this->seedFixedStudentThreads();
    }

    private function seedThreadsForCertification(Certification $certification): void
    {
        // 未回答(未解決・回答0件)を、必ず1件作る
        $this->seedOneThread(
            certification: $certification,
            title: "{$certification->name}について質問です(未回答)",
            body: '学習を進める中で疑問に思ったことがあります。詳しい方、教えていただけますでしょうか。',
            status: QaThreadStatus::Unresolved,
            repliesCount: 0,
        );

        // 対応中(未解決・回答あり)を、必ず1件作る
        $this->seedOneThread(
            certification: $certification,
            title: "{$certification->name}について質問です(対応中)",
            body: 'この部分について、まだ理解が難しいです。',
            status: QaThreadStatus::Unresolved,
            repliesCount: random_int(1, 2),
        );

        // 解決済みを、必ず1件作る
        $this->seedOneThread(
            certification: $certification,
            title: "{$certification->name}の学習方法について",
            body: '効率的な学習の進め方について、アドバイスをいただけますでしょうか。',
            status: QaThreadStatus::Resolved,
            repliesCount: random_int(1, 3),
        );

        // さらに、ランダムに0〜2件、追加のスレッドを散布(バリエーションを増やす)
        $extraCount = random_int(0, 2);
        for ($i = 0; $i < $extraCount; $i++) {
            $status = random_int(0, 1) === 0 ? QaThreadStatus::Unresolved : QaThreadStatus::Resolved;

            $this->seedOneThread(
                certification: $certification,
                title: "{$certification->name}に関する追加の質問({$i})",
                body: '追加で確認したいことがあります。',
                status: $status,
                repliesCount: $status === QaThreadStatus::Resolved ? random_int(1, 3) : random_int(0, 2),
            );
        }
    }

    private function seedOneThread(
        Certification $certification,
        string $title,
        string $body,
        QaThreadStatus $status,
        int $repliesCount,
    ): void {
        $author = User::query()->where('role', 'student')->inRandomOrder()->first();

        if ($author === null) {
            return;
        }

        $this->createThread(
            certification: $certification,
            author: $author,
            title: $title,
            body: $body,
            status: $status,
            repliesCount: $repliesCount,
        );
    }

    private function seedFixedStudentThreads(): void
    {
        $student = User::query()->where('email', 'student@certify-lms.test')->first();
        $certification = Certification::published()->first();

        if ($student === null || $certification === null) {
            return;
        }

        $this->createThread(
            certification: $certification,
            author: $student,
            title: '過去問の解き方について',
            body: '過去問を解いていて分からない箇所があります。どなたか教えていただけないでしょうか。',
            status: QaThreadStatus::Unresolved,
            repliesCount: 0,
        );

        $this->createThread(
            certification: $certification,
            author: $student,
            title: '学習計画の立て方について',
            body: '効率的な学習計画の立て方を教えていただき、無事に理解できました。',
            status: QaThreadStatus::Resolved,
            repliesCount: 1,
        );
    }

    private function createThread(
        Certification $certification,
        User $author,
        string $title,
        string $body,
        QaThreadStatus $status,
        int $repliesCount,
    ): QaThread {
        $createdAt = Carbon::now()->subDays(random_int(1, 30));

        $thread = QaThread::create([
            'certification_id' => $certification->id,
            'user_id' => $author->id,
            'title' => $title,
            'body' => $body,
            'status' => $status->value,
            'resolved_at' => $status === QaThreadStatus::Resolved ? $createdAt->copy()->addHours(3) : null,
        ]);
        $thread->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        if ($repliesCount > 0) {
            $this->seedReplies($thread, $repliesCount);
        }

        return $thread;
    }

    private function seedReplies(QaThread $thread, int $count): void
    {
        $coach = User::query()->where('role', 'coach')->inRandomOrder()->first();

        if ($coach === null) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $thread->replies()->create([
                'user_id' => $coach->id,
                'body' => 'ご質問ありがとうございます。こちらについてお答えします。',
            ]);
        }
    }
}
