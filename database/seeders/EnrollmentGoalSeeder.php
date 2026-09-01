<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 開発用 個人目標(enrollment_goals) シーダー。
 *
 * **設計思想**:
 *
 * 1. **各受講登録に、ランダムに目標を投入**: 目標を持つ受講登録・持たない受講登録、両方を
 *    混在させ、「まだ目標がありません」という空状態も、実機確認できるようにする。
 * 2. **固定 student のシナリオを、確実に用意**: student@certify-lms.test の受講登録に、
 *    未達成・達成済み、それぞれ1件以上を投入する。
 * 3. **目標期日は、一旦、未来のみ**: 過去日付の可否がヒアリング中のため、シーダーのデータは
 *    未来の日付に限定する。
 *
 * 依存順序: `UserSeeder` → `CertificationSeeder` → `EnrollmentSeeder` → 本Seeder。
 */
final class EnrollmentGoalSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = Enrollment::all();

        if ($enrollments->isEmpty()) {
            $this->command?->warn('EnrollmentGoalSeeder: 受講登録が存在しません。先にEnrollmentSeederを実行してください。');

            return;
        }

        foreach ($enrollments as $enrollment) {
            $goalCount = random_int(0, 3);

            for ($i = 0; $i < $goalCount; $i++) {
                $isAchieved = random_int(0, 1) === 1;

                EnrollmentGoal::factory()
                    ->forEnrollment($enrollment)
                    ->when($isAchieved, fn ($factory) => $factory->achieved())
                    ->create();
            }
        }

        $this->seedFixedStudentGoals();
    }

    private function seedFixedStudentGoals(): void
    {
        $student = User::query()->where('email', 'student@certify-lms.test')->first();

        if ($student === null) {
            return;
        }

        $enrollment = Enrollment::query()->where('user_id', $student->id)->first();

        if ($enrollment === null) {
            return;
        }

        EnrollmentGoal::factory()->forEnrollment($enrollment)->create([
            'title' => '過去問を5年分解き終える',
        ]);

        EnrollmentGoal::factory()->forEnrollment($enrollment)->achieved()->create([
            'title' => '基礎講座を全て視聴する',
        ]);
    }
}
