<?php

declare(strict_types=1);

namespace Tests\Feature\Http\QaThread;

use App\Enums\CertificationStatus;
use App\Enums\QaThreadStatus;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_filters_by_body(): void
    {
        $student = User::factory()->student()->create();
        $matched = QaThread::factory()->create(['body' => 'Laravelの認証について教えてください']);
        $unmatched = QaThread::factory()->create(['body' => '模試の合格点について']);

        $response = $this->actingAs($student)
            ->get(route('qa-board.index', ['keyword' => 'Laravel']));

        $response->assertSee($matched->title);
        $response->assertDontSee($unmatched->title);
    }

    public function test_certification_filter_narrows_results(): void
    {
        $student = User::factory()->student()->create();
        $targetCert = Certification::factory()->published()->create();
        $otherCert = Certification::factory()->published()->create();
        $matched = QaThread::factory()->forCertification($targetCert)->create();
        $unmatched = QaThread::factory()->forCertification($otherCert)->create();

        $response = $this->actingAs($student)
            ->get(route('qa-board.index', ['certification_id' => $targetCert->id]));

        $response->assertSee($matched->title);
        $response->assertDontSee($unmatched->title);
    }

    public function test_status_filter_narrows_results(): void
    {
        $student = User::factory()->student()->create();
        $resolved = QaThread::factory()->resolved()->create();
        $unresolved = QaThread::factory()->create();

        $response = $this->actingAs($student)
            ->get(route('qa-board.index', ['status' => QaThreadStatus::Resolved->value]));

        $response->assertSee($resolved->title);
        $response->assertDontSee($unresolved->title);
    }

    public function test_student_cannot_see_archived_certification_threads(): void
    {
        $student = User::factory()->student()->create();
        $archivedCert = Certification::factory()->create(['status' => CertificationStatus::Archived->value]);
        $thread = QaThread::factory()->forCertification($archivedCert)->create();

        $response = $this->actingAs($student)->get(route('qa-board.index'));

        $response->assertDontSee($thread->title);
    }

    public function test_admin_can_see_archived_certification_threads(): void
    {
        $admin = User::factory()->admin()->create();
        $archivedCert = Certification::factory()->create(['status' => CertificationStatus::Archived->value]);
        $thread = QaThread::factory()->forCertification($archivedCert)->create();

        $response = $this->actingAs($admin)->get(route('admin.qa-board.index'));

        $response->assertSee($thread->title);
    }

    public function test_admin_cannot_see_draft_certification_threads(): void
    {
        $admin = User::factory()->admin()->create();
        $draftCert = Certification::factory()->create(['status' => CertificationStatus::Draft->value]);
        $thread = QaThread::factory()->forCertification($draftCert)->create();

        $response = $this->actingAs($admin)->get(route('admin.qa-board.index'));

        $response->assertDontSee($thread->title);
    }
}
