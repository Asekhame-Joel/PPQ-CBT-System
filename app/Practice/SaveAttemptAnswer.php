<?php

namespace App\Practice;

use App\Enums\AttemptStatus;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveAttemptAnswer
{
    public function handle(
        User $student,
        int $attemptId,
        int $attemptQuestionId,
        ?int $selectedOptionId,
    ): AttemptAnswer {
        return DB::transaction(function () use ($student, $attemptId, $attemptQuestionId, $selectedOptionId): AttemptAnswer {
            $attempt = QuizAttempt::query()
                ->whereKey($attemptId)
                ->where('user_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status !== AttemptStatus::InProgress) {
                throw ValidationException::withMessages([
                    'answer' => 'This attempt is no longer in progress.',
                ]);
            }

            if ($attempt->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'answer' => 'Time has expired for this attempt.',
                ]);
            }

            $question = AttemptQuestion::query()
                ->whereKey($attemptQuestionId)
                ->where('quiz_attempt_id', $attempt->id)
                ->firstOrFail();
            $optionIds = collect($question->options_snapshot)->pluck('id')->map(fn ($id): int => (int) $id);

            if ($selectedOptionId !== null && ! $optionIds->containsStrict($selectedOptionId)) {
                throw ValidationException::withMessages([
                    'answer' => 'The selected answer does not belong to this question.',
                ]);
            }

            return $question->answer()->updateOrCreate([], [
                'selected_option_id' => $selectedOptionId,
                'is_correct' => null,
                'answered_at' => $selectedOptionId === null ? null : now(),
            ]);
        });
    }
}
