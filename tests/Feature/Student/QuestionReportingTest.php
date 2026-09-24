<?php

namespace Tests\Feature\Student;

use App\Enums\AttemptStatus;
use App\Filament\Student\Pages\PracticeResult;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionReportingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_report_an_attempt_question_from_results(): void
    {
        [$student, $attempt, $question] = $this->completedAttempt();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeResult::class, ['attempt' => $attempt])
            ->call('beginReport', $question->id)
            ->set('reportReason', 'wrong_explanation')
            ->set('reportComment', 'The explanation contradicts the marked answer.')
            ->call('submitReport')
            ->assertHasNoErrors()
            ->assertSet('reportingQuestionId', null);

        $this->assertDatabaseHas('question_reports', [
            'user_id' => $student->id,
            'attempt_question_id' => $question->id,
            'reason' => 'wrong_explanation',
            'report_text' => 'The explanation contradicts the marked answer.',
            'status' => 'pending',
        ]);
    }

    public function test_report_requires_a_meaningful_comment(): void
    {
        [$student, $attempt, $question] = $this->completedAttempt();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeResult::class, ['attempt' => $attempt])
            ->call('beginReport', $question->id)
            ->set('reportComment', 'bad')
            ->call('submitReport')
            ->assertHasErrors(['reportComment' => 'min']);

        $this->assertDatabaseCount('question_reports', 0);
    }

    public function test_student_cannot_report_a_question_from_another_attempt(): void
    {
        [$student, $attempt] = $this->completedAttempt();
        [, , $foreignQuestion] = $this->completedAttempt();
        Filament::setCurrentPanel(Filament::getPanel('student'));

        Livewire::actingAs($student)
            ->test(PracticeResult::class, ['attempt' => $attempt])
            ->call('beginReport', $foreignQuestion->id)
            ->assertStatus(404);

        $this->assertDatabaseCount('question_reports', 0);
    }

    /** @return array{User, QuizAttempt, AttemptQuestion} */
    private function completedAttempt(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->for($course)->create([
            'question_count' => 1,
            'status' => AttemptStatus::Submitted,
            'submitted_at' => now(),
            'correct_count' => 1,
            'incorrect_count' => 0,
            'unanswered_count' => 0,
            'score_percentage' => 100,
        ]);
        $question = AttemptQuestion::factory()->for($attempt)->create(['position' => 1]);
        AttemptAnswer::factory()->for($question)->create();

        return [$student, $attempt, $question];
    }
}
