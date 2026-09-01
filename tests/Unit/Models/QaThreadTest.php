<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * QaThread モデルのリレーション・Castを検証するUnitテスト。
 * 3リレーション(certification / user / replies) + 1 cast(status QaThreadStatus)を網羅する。
 */
class QaThreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_certification_relation_returns_parent_certification(): void
    {
        $certification = Certification::factory()->published()->create();
        $thread = QaThread::factory()->forCertification($certification)->create();

        $this->assertTrue($thread->certification->is($certification));
    }

    public function test_user_relation_returns_author(): void
    {
        $author = User::factory()->student()->create();
        $thread = QaThread::factory()->forUser($author)->create();

        $this->assertTrue($thread->user->is($author));
    }

    public function test_replies_relation_returns_attached_replies(): void
    {
        $thread = QaThread::factory()->create();
        $thread->replies()->create(['user_id' => User::factory()->coach()->create()->id, 'body' => '回答1']);
        $thread->replies()->create(['user_id' => User::factory()->coach()->create()->id, 'body' => '回答2']);
        QaThread::factory()->create();  // 別スレッドの回答は含まれないことを確認するため

        $this->assertCount(2, $thread->replies);
    }

    public function test_status_cast_returns_enum_instance(): void
    {
        $thread = QaThread::factory()->create();

        $this->assertInstanceOf(QaThreadStatus::class, $thread->status);
    }
}
