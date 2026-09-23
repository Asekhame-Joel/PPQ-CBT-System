<?php

namespace Database\Factories;

use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttemptAnswer>
 */
class AttemptAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attempt_question_id' => AttemptQuestion::factory(),
            'selected_option_id' => 101,
            'is_correct' => true,
            'answered_at' => now(),
        ];
    }
}
