<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_thread(): void
    {
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->published()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => $certification->id,
                'title' => 'テストの質問タイトル',
                'body' => 'テストの質問本文です。',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('qa_threads', [
            'certification_id' => $certification->id,
            'user_id' => $student->id,
            'title' => 'テストの質問タイトル',
        ]);
    }

    public function test_title_is_required(): void
    {
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->published()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => $certification->id,
                'title' => '',
                'body' => 'テストの本文です。',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_title_cannot_exceed_200_characters(): void
    {
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->published()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => $certification->id,
                'title' => str_repeat('あ', 201),
                'body' => 'テストの本文です。',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_body_is_required(): void
    {
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->published()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => $certification->id,
                'title' => 'テストのタイトル',
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_body_cannot_exceed_5000_characters(): void
    {
        $student = User::factory()->student()->create();
        $certification = Certification::factory()->published()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => $certification->id,
                'title' => 'テストのタイトル',
                'body' => str_repeat('あ', 5001),
            ])
            ->assertSessionHasErrors('body');
    }

    public function test_nonexistent_certification_id_is_rejected(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('qa-board.store'), [
                'certification_id' => 'nonexistent-id',
                'title' => 'テストのタイトル',
                'body' => 'テストの本文です。',
            ])
            ->assertSessionHasErrors('certification_id');
    }
}
