<?php

namespace Tests\Feature\Practice;

use App\Enums\AttemptStatus;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Practice\SubmitPracticeAttempt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitPracticeAttemptTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_scores_correct_incorrect_and_unanswered_questions(): void
    {
        $student = User::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->create([
            'question_count' => 3,
            'expires_at' => now()->addMinutes(10),
        ]);
        $correct = $this->addQuestion($attempt, 1, 101);
        $incorrect = $this->addQuestion($attempt, 2, 102);
        $unanswered = $this->addQuestion($attempt, 3, null);

        $result = app(SubmitPracticeAttempt::class)->handle($student, $attempt->id);

        $this->assertSame(AttemptStatus::Submitted, $result->status);
        $this->assertSame(1, $result->correct_count);
        $this->assertSame(1, $result->incorrect_count);
        $this->assertSame(1, $result->unanswered_count);
        $this->assertSame('33.33', $result->score_percentage);
        $this->assertNotNull($result->submitted_at);
        $this->assertTrue($correct->answer->fresh()->is_correct);
        $this->assertFalse($incorrect->answer->fresh()->is_correct);
        $this->assertNull($unanswered->answer->fresh()->is_correct);
    }

    public function test_expired_attempt_is_scored_and_marked_expired(): void
    {
        $student = User::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->create([
            'question_count' => 1,
            'expires_at' => now()->subSecond(),
        ]);
        $this->addQuestion($attempt, 1, 101);

        $result = app(SubmitPracticeAttempt::class)->handle($student, $attempt->id, requireExpired: true);

        $this->assertSame(AttemptStatus::Expired, $result->status);
        $this->assertSame(1, $result->correct_count);
        $this->assertSame('100.00', $result->score_percentage);
    }

    public function test_expiry_submission_cannot_be_triggered_early(): void
    {
        $student = User::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->create([
            'expires_at' => now()->addMinute(),
        ]);

        $this->expectException(ValidationException::class);

        app(SubmitPracticeAttempt::class)->handle($student, $attempt->id, requireExpired: true);
    }

    public function test_submission_is_idempotent(): void
    {
        $student = User::factory()->create();
        $attempt = QuizAttempt::factory()->for($student)->create([
            'question_count' => 1,
            'expires_at' => now()->addMinute(),
        ]);
        $question = $this->addQuestion($attempt, 1, 101);
        $submitter = app(SubmitPracticeAttempt::class);

        $first = $submitter->handle($student, $attempt->id);
        $question->answer->update(['selected_option_id' => 102]);
        $second = $submitter->handle($student, $attempt->id);

        $this->assertSame($first->submitted_at->getTimestamp(), $second->submitted_at->getTimestamp());
        $this->assertSame(1, $second->correct_count);
        $this->assertSame(0, $second->incorrect_count);
    }

    public function test_student_cannot_submit_another_students_attempt(): void
    {
        $attempt = QuizAttempt::factory()->create();
        $otherStudent = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(SubmitPracticeAttempt::class)->handle($otherStudent, $attempt->id);
    }

    private function addQuestion(QuizAttempt $attempt, int $position, ?int $selectedOptionId): AttemptQuestion
    {
        $question = AttemptQuestion::factory()->for($attempt)->create([
            'position' => $position,
            'options_snapshot' => [
                ['id' => 101, 'text' => 'Correct'],
                ['id' => 102, 'text' => 'Wrong'],
            ],
            'correct_option_snapshot' => 101,
        ]);
        AttemptAnswer::factory()->for($question)->create([
            'selected_option_id' => $selectedOptionId,
            'is_correct' => null,
            'answered_at' => $selectedOptionId === null ? null : now(),
        ]);

        return $question->load('answer');
    }
}
