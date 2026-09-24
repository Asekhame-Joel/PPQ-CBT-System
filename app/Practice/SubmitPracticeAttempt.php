<?php

namespace App\Practice;

use App\Enums\AttemptStatus;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitPracticeAttempt
{
    public function handle(User $student, int $attemptId, bool $requireExpired = false): QuizAttempt
    {
        return DB::transaction(function () use ($student, $attemptId, $requireExpired): QuizAttempt {
            $attempt = QuizAttempt::query()
                ->whereKey($attemptId)
                ->where('user_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status !== AttemptStatus::InProgress) {
                return $attempt;
            }

            $hasExpired = $attempt->expires_at->lessThanOrEqualTo(now());

            if ($requireExpired && ! $hasExpired) {
                throw ValidationException::withMessages([
                    'attempt' => 'This attempt still has time remaining.',
                ]);
            }

            $questions = $attempt->questions()->with('answer')->get();
            $correctCount = 0;
            $incorrectCount = 0;
            $unansweredCount = 0;

            foreach ($questions as $question) {
                $answer = $question->answer()->firstOrCreate();
                $selectedOptionId = $answer->selected_option_id;

                if ($selectedOptionId === null) {
                    $unansweredCount++;
                    $answer->update(['is_correct' => null]);

                    continue;
                }

                $isCorrect = $selectedOptionId === $question->correct_option_snapshot;
                $answer->update(['is_correct' => $isCorrect]);

                if ($isCorrect) {
                    $correctCount++;
                } else {
                    $incorrectCount++;
                }
            }

            $questionCount = $questions->count();
            $scorePercentage = $questionCount === 0
                ? 0
                : round(($correctCount / $questionCount) * 100, 2);

            $attempt->update([
                'submitted_at' => now(),
                'status' => $hasExpired ? AttemptStatus::Expired : AttemptStatus::Submitted,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percentage' => $scorePercentage,
            ]);

            return $attempt->refresh();
        });
    }
}
