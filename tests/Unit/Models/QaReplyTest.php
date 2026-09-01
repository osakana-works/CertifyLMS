<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * QaReply モデルのリレーションを検証するUnitテスト。
 * 2リレーション(qaThread / user)を網羅する。
 */
class QaReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_qa_thread_relation_returns_parent_thread(): void
    {
        $thread = QaThread::factory()->create();
        $reply = QaReply::factory()->forThread($thread)->create();

        $this->assertTrue($reply->qaThread->is($thread));
    }

    public function test_user_relation_returns_author(): void
    {
        $author = User::factory()->coach()->create();
        $reply = QaReply::factory()->forUser($author)->create();

        $this->assertTrue($reply->user->is($author));
    }
}
