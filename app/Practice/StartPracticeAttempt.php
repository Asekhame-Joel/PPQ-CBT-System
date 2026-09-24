<?php

namespace App\Practice;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class StartPracticeAttempt
{
    public function handle(
        User $student,
        Course $course,
        int $questionCount,
        int $durationMinutes,
        bool $randomizeQuestions,
        bool $randomizeOptions,
    ): QuizAttempt {
        $this->validateRequest($student, $course, $questionCount, $durationMinutes);

        return DB::transaction(function () use (
            $student,
            $course,
            $questionCount,
            $durationMinutes,
            $randomizeQuestions,
            $randomizeOptions,
        ): QuizAttempt {
            $questions = Question::query()
                ->active()
                ->where('course_id', $course->id)
                ->with('options')
                ->when(
                    $randomizeQuestions,
                    fn (Builder $query): Builder => $query->inRandomOrder(),
                    fn (Builder $query): Builder => $query->orderBy('id'),
                )
                ->limit($questionCount)
                ->get();

            if ($questions->count() !== $questionCount) {
                throw ValidationException::withMessages([
                    'question_count' => 'The requested number of active questions is no longer available.',
                ]);
            }

            $startedAt = now();
            $attempt = QuizAttempt::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'attempt_type' => AttemptType::NewPractice,
                'question_count' => $questionCount,
                'duration_minutes' => $durationMinutes,
                'started_at' => $startedAt,
                'expires_at' => $startedAt->copy()->addMinutes($durationMinutes),
                'status' => AttemptStatus::InProgress,
            ]);

            foreach ($questions as $index => $question) {
                $options = $randomizeOptions
                    ? $question->options->shuffle()->values()
                    : $question->options->sortBy('sort_order')->values();
                $correctOptions = $options->where('is_correct', true);

                if ($options->count() < 2 || $correctOptions->count() !== 1) {
                    throw new LogicException("Question {$question->id} does not have a valid answer set.");
                }

                $attemptQuestion = $attempt->questions()->create([
                    'question_id' => $question->id,
                    'position' => $index + 1,
                    'question_snapshot' => $question->question_text,
                    'options_snapshot' => $options->map(fn ($option): array => [
                        'id' => $option->id,
                        'text' => $option->option_text,
                    ])->all(),
                    'correct_option_snapshot' => $correctOptions->first()->id,
                    'explanation_snapshot' => $question->explanation,
                ]);

                $attemptQuestion->answer()->create();
            }

            return $attempt->load('questions.answer');
        });
    }

    private function validateRequest(
        User $student,
        Course $course,
        int $questionCount,
        int $durationMinutes,
    ): void {
        if (! $course->is_active || ! CourseAccess::query()
            ->available()
            ->where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'course' => 'Course access is no longer available.',
            ]);
        }

        $availableQuestionCount = $course->questions()->active()->count();
        $maximumQuestionCount = min($course->max_question_count, $availableQuestionCount);

        if ($questionCount < $course->min_question_count || $questionCount > $maximumQuestionCount) {
            throw ValidationException::withMessages([
                'question_count' => "Choose between {$course->min_question_count} and {$maximumQuestionCount} questions.",
            ]);
        }

        if ($durationMinutes < $course->min_duration || $durationMinutes > $course->max_duration) {
            throw ValidationException::withMessages([
                'duration_minutes' => "Choose between {$course->min_duration} and {$course->max_duration} minutes.",
            ]);
        }
    }
}
