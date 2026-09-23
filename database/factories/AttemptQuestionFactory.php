<?php

namespace Database\Factories;

use App\Models\AttemptQuestion;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttemptQuestion>
 */
class AttemptQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_attempt_id' => QuizAttempt::factory(),
            'question_id' => null,
            'position' => fake()->unique()->numberBetween(1, 1000),
            'question_snapshot' => 'What does CPU stand for?',
            'options_snapshot' => [
                ['id' => 101, 'text' => 'Central Processing Unit'],
                ['id' => 102, 'text' => 'Computer Processing Utility'],
            ],
            'correct_option_snapshot' => 101,
            'explanation_snapshot' => 'CPU means Central Processing Unit.',
        ];
    }
}
