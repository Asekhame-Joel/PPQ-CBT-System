<?php

namespace Database\Factories;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'parent_attempt_id' => null,
            'attempt_type' => AttemptType::NewPractice,
            'question_count' => 50,
            'duration_minutes' => 30,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'submitted_at' => null,
            'status' => AttemptStatus::InProgress,
            'correct_count' => null,
            'incorrect_count' => null,
            'unanswered_count' => null,
            'score_percentage' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submitted_at' => now(),
            'status' => AttemptStatus::Submitted,
            'correct_count' => 40,
            'incorrect_count' => 8,
            'unanswered_count' => 2,
            'score_percentage' => 80,
        ]);
    }
}
