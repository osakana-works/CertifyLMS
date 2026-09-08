<?php

declare(strict_types=1);

namespace App\UseCases\Chat;

use App\Events\ChatMessageSent;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use App\Notifications\Chat\ChatMessageReceivedNotification;
use Illuminate\Support\Facades\DB;

/**
 * ChatRoom にメッセージを INSERT し、送信者の既読時刻を更新したうえで Broadcast を発火する Action。
 *
 * - INSERT 後、ChatMessage::booted() が `chat_rooms.last_message_at` を denormalize 更新する
 * - 送信者自身の `ChatMember.last_read_at = now()` を UPDATE(自分のメッセージは未読としてカウントしない)
 * - 通信失敗が DB 整合性に波及しないよう Pusher Broadcast は `DB::afterCommit()` で送る
 * - 担当コーチ未割当の判定は Controller 側で実施済(`CertificationCoachNotAssignedForChatException` 振り分け)
 * - 送信者以外の ChatMember へ、通知(database + mail)を afterCommit で送る(TODO: 対象の利用状態による
 *   除外条件はコーチへのヒアリング待ち。現状は room に参加している全員に送る)
 */
final class StoreMessageAction
{
    /**
     * @param array{body: string} $validated
     */
    public function __invoke(User $sender, ChatRoom $room, array $validated): ChatMessage
    {
        return DB::transaction(function () use ($sender, $room, $validated) {
            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'sender_user_id' => $sender->id,
                'body' => $validated['body'],
            ]);

            ChatMember::query()
                ->where('chat_room_id', $room->id)
                ->where('user_id', $sender->id)
                ->update(['last_read_at' => now()]);

            DB::afterCommit(function () use ($message, $sender, $room): void {
                broadcast(new ChatMessageSent($message->load('sender')))->toOthers();

                $recipients = User::query()
                    ->whereIn('id', ChatMember::query()
                            ->forRoom($room)
                            ->where('user_id', '<>', $sender->id)
                            ->pluck('user_id'))
                    ->get();

                foreach ($recipients as $recipient) {
                    $recipient->notify(new ChatMessageReceivedNotification($message));
                }
            });

            return $message;
        });
    }
}