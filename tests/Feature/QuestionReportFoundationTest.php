<?php

namespace Tests\Feature;

use App\Enums\QuestionReportStatus;
use App\Models\AttemptQuestion;
use App\Models\Question;
use App\Models\QuestionReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuestionReportFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_report_links_reporter_source_question_and_attempt_snapshot(): void
    {
        $student = User::factory()->create();
        $question = Question::factory()->create();
        $attemptQuestion = AttemptQuestion::factory()->create(['question_id' => $question->id]);
        $report = QuestionReport::factory()
            ->for($student, 'reporter')
            ->for($question)
            ->for($attemptQuestion)
            ->create();

        $this->assertTrue($report->reporter->is($student));
        $this->assertTrue($report->question->is($question));
        $this->assertTrue($report->attemptQuestion->is($attemptQuestion));
        $this->assertTrue($student->questionReports->contains($report));
        $this->assertTrue($question->reports->contains($report));
        $this->assertTrue($attemptQuestion->reports->contains($report));
        $this->assertSame(QuestionReportStatus::Pending, $report->status);
    }

    public function test_resolved_report_records_admin_resolution(): void
    {
        $report = QuestionReport::factory()->resolved()->create();

        $this->assertSame(QuestionReportStatus::Resolved, $report->status);
        $this->assertNotNull($report->admin_notes);
        $this->assertNotNull($report->resolved_at);
        $this->assertTrue($report->resolver->resolvedQuestionReports->contains($report));
    }

    public function test_pending_scope_excludes_completed_reports(): void
    {
        $pendingReport = QuestionReport::factory()->create();
        QuestionReport::factory()->resolved()->create();

        $report = QuestionReport::pending()->sole();

        $this->assertTrue($report->is($pendingReport));
    }
}
