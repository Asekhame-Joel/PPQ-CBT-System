<?php

namespace Tests\Feature;

use App\Enums\AttemptStatus;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubmitExpiredAttemptsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_submits_expired_attempts_and_leaves_active_ones_in_progress(): void
    {
        $expiredAttempt = QuizAttempt::factory()->create([
            'question_count' => 2,
            'expires_at' => now()->subMinute(),
        ]);
        $correctQuestion = AttemptQuestion::factory()->for($expiredAttempt, 'quizAttempt')->create([
            'position' => 1,
            'correct_option_snapshot' => 101,
        ]);
        AttemptAnswer::factory()->for($correctQuestion, 'attemptQuestion')->create([
            'selected_option_id' => 101,
        ]);
        $unansweredQuestion = AttemptQuestion::factory()->for($expiredAttempt, 'quizAttempt')->create([
            'position' => 2,
        ]);
        AttemptAnswer::factory()->for($unansweredQuestion, 'attemptQuestion')->create([
            'selected_option_id' => null,
            'is_correct' => null,
            'answered_at' => null,
        ]);
        $activeAttempt = QuizAttempt::factory()->create(['expires_at' => now()->addMinute()]);

        $this->artisan('exam:submit-expired-attempts')
            ->expectsOutput('Submitted 1 expired practice attempt(s).')
            ->assertExitCode(0);

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $expiredAttempt->id,
            'status' => AttemptStatus::Expired->value,
            'correct_count' => 1,
            'incorrect_count' => 0,
            'unanswered_count' => 1,
            'score_percentage' => 50,
        ]);
        $this->assertDatabaseHas('attempt_answers', [
            'attempt_question_id' => $unansweredQuestion->id,
            'is_correct' => null,
        ]);
        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $activeAttempt->id,
            'status' => AttemptStatus::InProgress->value,
        ]);
    }
}
