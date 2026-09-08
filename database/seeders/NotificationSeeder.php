<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MeetingStatus;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Enrollment;
use App\Models\Meeting;
use App\Models\QaThread;
use App\Models\User;
use App\Notifications\Chat\ChatMessageReceivedNotification;
use App\Notifications\Meeting\MeetingReservedNotification;
use App\Notifications\QaThread\QaReplyReceivedNotification;
use App\Notifications\QaThread\QaThreadCreatedNotification;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * 開発用 通知(notifications テーブル)シーダー。
 *
 * 固定アカウント(student@certify-lms.test / coach@certify-lms.test)に対し、
 * 既存シーダー(ChatSeeder / QaThreadSeeder / MentoringSeeder)が投入済みの実データを
 * 元に実際の Notification クラスを発火させ、既読・未読が混在した状態で
 * 通知一覧・ベル未読数バッジの動作確認ができるようにする。
 *
 * 依存順序: UserSeeder → EnrollmentSeeder → ChatSeeder → QaThreadSeeder → MentoringSeeder → 本 Seeder。
 */
final class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::query()->where('email', 'student@certify-lms.test')->first();
        $coach = User::query()->where('email', 'coach@certify-lms.test')->first();

        if ($student === null || $coach === null) {
            $this->command?->warn('NotificationSeeder: 固定アカウントが存在しません。先に UserSeeder を実行してください。');

            return;
        }

        $this->seedChatNotifications($student, $coach);
        $this->seedQaNotifications($student, $coach);
        $this->seedMeetingNotifications($coach);
        $this->seedPaginationFiller($student);
    }

    /**
     * 一覧のページネーション(20 件/ページ)の動作確認ができるよう、student 宛てに
     * 実データを介さない簡易通知を追加投入し、既読・未読を混在させる。
     * 実際の Notification クラスは使わず、DatabaseNotification に直接書き込む
     * (大量の ChatMessage 等の実体を作らずに済ませるため)。
     */
    private function seedPaginationFiller(User $student, int $count = 28): void
    {
        $now = now();

        for ($i = 0; $i < $count; $i++) {
            $createdAt = $now->copy()->subHours($i + 1);

            $notification = new DatabaseNotification;
            $notification->id = (string) Str::uuid();
            $notification->type = ChatMessageReceivedNotification::class;
            $notification->notifiable_type = User::class;
            $notification->notifiable_id = $student->id;
            $notification->data = [
                'notification_type' => 'chat_message_received',
                'title' => 'コーチ太郎 さんからメッセージ',
                'message' => "ページネーション確認用のサンプル通知です({$i}件目)。",
                'action_url' => route('notifications.index'),
            ];
            $notification->read_at = $i % 3 === 0 ? null : $createdAt->copy()->addMinutes(10);
            $notification->created_at = $createdAt;
            $notification->updated_at = $createdAt;
            $notification->save();
        }
    }

    /**
     * ChatSeeder の固定会話(student ⇄ coach の往復)を元に、双方向に 1 件ずつ通知を作る。
     * student → coach 分は既読化、coach → student 分は未読のまま残す(実際の既読状況と整合させる)。
     */
    private function seedChatNotifications(User $student, User $coach): void
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->orderBy('created_at')
            ->first();

        if ($enrollment === null) {
            return;
        }

        $room = ChatRoom::query()->where('enrollment_id', $enrollment->id)->first();
        if ($room === null) {
            return;
        }

        $messages = ChatMessage::query()
            ->where('chat_room_id', $room->id)
            ->orderBy('created_at')
            ->get();

        $firstFromStudent = $messages->firstWhere('sender_user_id', $student->id);
        $lastFromCoach = $messages->where('sender_user_id', $coach->id)->last();

        if ($firstFromStudent !== null) {
            $coach->notify(new ChatMessageReceivedNotification($firstFromStudent));
            $coach->notifications()->latest()->first()?->markAsRead();
        }

        if ($lastFromCoach !== null) {
            $student->notify(new ChatMessageReceivedNotification($lastFromCoach));
        }
    }

    /**
     * QaThreadSeeder の固定スレッドを元に、student 宛て(回答通知・既読)と
     * coach 宛て(新規投稿通知・未読)を 1 件ずつ作る。
     */
    private function seedQaNotifications(User $student, User $coach): void
    {
        $resolvedThread = QaThread::query()
            ->where('user_id', $student->id)
            ->where('title', '学習計画の立て方について')
            ->first();

        $reply = $resolvedThread?->replies()->first();
        if ($reply !== null) {
            $student->notify(new QaReplyReceivedNotification($reply));
            $student->notifications()->latest()->first()?->markAsRead();
        }

        $unresolvedThread = QaThread::query()
            ->where('user_id', $student->id)
            ->where('title', '過去問の解き方について')
            ->first();

        if ($unresolvedThread !== null) {
            $coach->notify(new QaThreadCreatedNotification($unresolvedThread));
        }
    }

    /**
     * MentoringSeeder の固定 reserved 面談を元に、coach 宛て予約通知(未読)を作る。
     */
    private function seedMeetingNotifications(User $coach): void
    {
        $reserved = Meeting::query()
            ->where('coach_id', $coach->id)
            ->where('status', MeetingStatus::Reserved->value)
            ->orderByDesc('scheduled_at')
            ->first();

        if ($reserved !== null) {
            $coach->notify(new MeetingReservedNotification($reserved));
        }
    }
}
